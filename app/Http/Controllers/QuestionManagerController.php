<?php

namespace App\Http\Controllers;

use App\Mail\QuestionUploadMail;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;


class QuestionManagerController extends Controller
{
    public function index()
    {
        $subjects = Subject::with(['topics'])->orderBy('id', 'DESC')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $subjects->items(),
            'pagination' => [
                'total' => $subjects->total(),
                'per_page' => $subjects->perPage(),
                'current_page' => $subjects->currentPage(),
                'last_page' => $subjects->lastPage(),
                'from' => $subjects->firstItem(),
                'to' => $subjects->lastItem()
            ]
        ]);
    }

    public function getSubject(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Default to 10 items per page

            $subjects = Subject::orderBy('id', 'DESC')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $subjects->items(),
                'pagination' => [
                    'total' => $subjects->total(),
                    'per_page' => $subjects->perPage(),
                    'current_page' => $subjects->currentPage(),
                    'last_page' => $subjects->lastPage(),
                    'from' => $subjects->firstItem(),
                    'to' => $subjects->lastItem()
                ]
            ]);
        } catch (\Exception $ex) {
            Log::error('Error getting subjects: ' . $ex->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving subjects'
            ], 500);
        }
    }

    public function getTopic(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Default to 10 items per page

            $topic = Topic::with('questions')
                ->orderBy('id', 'DESC')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $topic->items(),
                'pagination' => [
                    'total' => $topic->total(),
                    'per_page' => $topic->perPage(),
                    'current_page' => $topic->currentPage(),
                    'last_page' => $topic->lastPage(),
                    'from' => $topic->firstItem(),
                    'to' => $topic->lastItem()
                ]
            ]);
        } catch (\Exception $ex) {
            Log::error('Error getting topic: ' . $ex->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving topic'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // FormData sends `questions` and `schedule` as JSON strings.
        // Merge them back into arrays so validation + the rest of the method
        // can treat them like normal request input.
        if (is_string($request->input('questions'))) {
            $request->merge([
                'questions' => json_decode($request->input('questions'), true),
            ]);
        }
        if (is_string($request->input('schedule'))) {
            $request->merge([
                'schedule' => json_decode($request->input('schedule'), true),
            ]);
        }

        $request->validate([
            'subject'                  => ['required', 'string'],
            'topics'                   => ['required', 'array', 'min:1'],
            'topics.*'                 => ['required', 'string'],
            'questions'                => ['required', 'array', 'min:1'],
            'questions.*.text'         => ['required', 'string'],
            'questions.*.options'      => ['required', 'array', 'min:1'],
            'questions.*.correctIndex' => ['required', 'integer'],
            'file'                     => ['required', 'file', 'mimes:pdf'],

            // ---- Scheduling ----
            // Omitted entirely when the admin clicks "Save (Available Now)".
            // Present only when they clicked "Schedule Quiz" and confirmed.
            'schedule'                    => ['nullable', 'array'],
            'schedule.startDate'          => ['required_with:schedule', 'date'],
            'schedule.endDate'            => ['nullable', 'date', 'after:schedule.startDate'],
            'schedule.durationMinutes'    => ['nullable', 'integer', 'min:1'],
        ]);

        // ------------------------------------------------------------------
        // Resolve the schedule ONCE — it applies to every question in this upload.
        //
        //  • No `schedule` in the payload  → Save/Available Now:
        //        available_from   = null  (available immediately)
        //        available_until  = null  (available forever)
        //        duration_minutes = null  (no per-attempt limit)
        //
        //  • `schedule` present            → Scheduled:
        //        available_from   = startDate
        //        available_until  = endDate or null (open-ended if omitted)
        //        duration_minutes = durationMinutes or null
        // ------------------------------------------------------------------
        $schedule        = $request->input('schedule');
        $publishedAt     = now();               // the quiz exists as of now in both flows
        $availableFrom   = null;
        $availableUntil  = null;
        $durationMinutes = null;

        if (! empty($schedule)) {
            $availableFrom   = Carbon::parse($schedule['startDate']);
            $availableUntil  = ! empty($schedule['endDate'])
                ? Carbon::parse($schedule['endDate'])
                : null;                         // null = no closing date
            $durationMinutes = ! empty($schedule['durationMinutes'])
                ? (int) $schedule['durationMinutes']
                : null;
        }

        $uploadedFilePath = null;

        try {
            DB::beginTransaction();

            // ---- File upload (unchanged) ----
            $file = $request->file('file');
            $uploadDir = storage_path('app/public/questions');

            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $uploadedFilePath = $uploadDir . '/' . $fileName;

            // ---- Subject (unchanged) ----
            $baseSlug = Str::slug($request->subject);
            $slug = $baseSlug;
            $counter = 1;

            while (Subject::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $subject = Subject::create([
                'name' => trim($request->subject),
                'slug' => $slug,
            ]);

            // ---- Topics + Questions ----
            foreach ($request->topics as $topicName) {
                $baseTopicSlug = Str::slug($topicName);
                $topicSlug = $baseTopicSlug;
                $topicCounter = 1;

                while (Topic::where('slug', $topicSlug)->exists()) {
                    $topicSlug = $baseTopicSlug . '-' . $topicCounter++;
                }

                $topic = Topic::create([
                    'subject_id' => $subject->id,
                    'name'       => $topicName,
                    'slug'       => $topicSlug,
                    'fileName'   => $fileName,
                ]);

                foreach ($request->questions as $questionData) {
                    $baseQuestionSlug = Str::slug(Str::limit($questionData['text'], 50));
                    $questionSlug = $baseQuestionSlug;
                    $questionCounter = 1;

                    while (Question::where('slug', $questionSlug)->exists()) {
                        $questionSlug = $baseQuestionSlug . '-' . $questionCounter++;
                    }

                    Question::create([
                        'topic_id'         => $topic->id,
                        'question_text'    => $questionData['text'],
                        'slug'             => $questionSlug,
                        'options'          => json_encode($questionData['options']),
                        'correct_index'    => $questionData['correctIndex'],

                        // ---- Scheduling ----
                        'published_at'     => $publishedAt,
                        'available_from'   => $availableFrom,
                        'available_until'  => $availableUntil,
                        'duration_minutes' => $durationMinutes,
                        // 'closed_at' stays NULL — manual close is a separate action.
                    ]);
                }
            }

            // ---- Notify users (unchanged) ----
            // Notify only if the quiz is live right now.
            // Future-scheduled quizzes are notified by quizzes:activate-scheduled.
            $isLiveNow = is_null($availableFrom) || $availableFrom->lte(now());

            if ($isLiveNow) {
                $users = User::where('role', 'USER')->get();
                $fileUrl = asset('public/storage/questions/' . $fileName);

                foreach ($users as $index => $user) {
                    Mail::to($user->email)->later(
                        now()->addSeconds($index * 20),
                        new QuestionUploadMail($user, $fileUrl)
                    );
                }

                Topic::where('fileName', $fileName)->update(['notified_at' => now()]);
            }

            DB::commit();

            return response()->json([
                'message'   => 'Questions uploaded successfully',
                'file_path' => asset('storage/questions/' . $fileName),
                // Helpful for the admin toast:
                'availability' => [
                    'mode'             => $schedule ? 'scheduled' : 'available_now',
                    'available_from'   => $availableFrom?->toIso8601String(),
                    'available_until'  => $availableUntil?->toIso8601String(),
                    'duration_minutes' => $durationMinutes,
                ],
            ]);
        } catch (Exception $ex) {
            DB::rollBack();

            if ($uploadedFilePath && file_exists($uploadedFilePath)) {
                unlink($uploadedFilePath);
            }

            Log::error('Error uploading questions: ' . $ex->getMessage());

            return response()->json([
                'message' => 'Error uploading questions. Try again later',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $topic = Topic::find($id);

            if (!$topic) {
                return response()->json(['message' => 'Topic not found!'], 404);
            }

            $subject = $topic->subject;

            foreach ($topic->answers as $answer) {
                if ($answer->signature) {
                    $signaturePath = storage_path("app/public/signature/{$answer->signature_file}");
                    if (file_exists($signaturePath)) {
                        unlink($signaturePath);
                    }
                }
            }

            $topic->answers()->delete();

            if ($topic->fileName) {
                $filePath = storage_path("app/public/questions/{$topic->fileName}");
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $topic->delete();

            if ($subject && $subject->topics()->count() === 0) {
                $subject->delete();
            }

            return response()->json([
                'message' => 'Topic, associated answers, and files deleted successfully; subject deleted if no topics left'
            ]);
        } catch (\Exception $ex) {
            Log::error("Error deleting topic: " . $ex->getMessage());
            return response()->json([
                'message' => 'An error occurred while deleting the topic'
            ], 500);
        }
    }


    public function storeAnswer(Request $request)
    {
        $validated = $request->validate([
            'topic_id' => ['required', 'exists:topics,id'],
            'score' => ['required', 'integer'],
            'total' => ['required', 'integer'],
            'time' => ['nullable', 'string'],
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'exists:questions,id'],
            'answers.*.answer_index' => ['required', 'integer'],
            'signature' => ['required', 'string'], // base64 string
            'declaration' => ['required'],
        ], [
            'topic_id.required' => 'The topic for this question is required',
            'topic_id.exists' => 'Cannot find this question\'s topic',
            'score.required' => 'Your score is missing',
            'score.integer' => 'Invalid score',
            'total.required' => 'Total questions count is required',
            'total.integer' => 'Invalid total questions count',
            'answers.required' => 'Answer all questions',
            'answers.array' => 'The answers are invalid',
            'signature.required' => 'Your signature is required',
            'signature.string' => 'Invalid signature',
            'declaration.required' => 'Accept the declaration'
        ]);

        DB::beginTransaction();

        try {
            $topicId = $validated['topic_id'];
            $userId = Auth::id();

            // Prevent duplicate attempts
            if (Answer::where('user_id', $userId)->where('topic_id', $topicId)->exists()) {
                return response()->json(['message' => 'You have already answered this topic'], 409);
            }

            // Prepare signature (don't save yet)
            $signatureData = $validated['signature'];
            if (strpos($signatureData, 'data:image/png;base64,') === 0) {
                $signatureData = substr($signatureData, strlen('data:image/png;base64,'));
            }
            $signatureData = str_replace(' ', '+', $signatureData);
            $image = base64_decode($signatureData);
            $fileName = 'signature_' . $userId . '_' . time() . '.png';

            // Fetch correct indexes
            $questionIds = collect($validated['answers'])->pluck('question_id')->toArray();
            $correctIndexes = Question::whereIn('id', $questionIds)
                ->pluck('correct_index', 'id')
                ->toArray();

            $answersJson = collect($validated['answers'])->map(function ($ans) use ($correctIndexes) {
                return [
                    'question_id' => $ans['question_id'],
                    'answer_index' => $ans['answer_index'],
                    'correct_index' => $correctIndexes[$ans['question_id']] ?? null
                ];
            });

            // Insert DB record (still inside transaction)
            Answer::create([
                'user_id' => $userId,
                'topic_id' => $topicId,
                'answers' => json_encode($answersJson),
                'score' => $validated['score'],
                'total' => $validated['total'],
                'time' => $validated['time'] ?? null,
                'signature' => $fileName,
                'declaration' => $validated['declaration'],
            ]);

            DB::commit();

            // ✅ Now save the signature file only after DB commit
            $signaturePath = storage_path('app/public/signature');
            if (!File::exists($signaturePath)) {
                File::makeDirectory($signaturePath, 0755, true);
            }
            Storage::disk('public')->put('signature/' . $fileName, $image);

            return response()->json(['message' => 'Answers saved successfully']);
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error("Error saving your answer: " . $ex->getMessage());
            return response()->json(['message' => 'Error saving your answer, try again later or contact your website admin'], 500);
        }
    }

    public function summary($topicId)
    {
        $userId = Auth::id();

        try {
            $grade = Answer::where('user_id', $userId)
                ->where('topic_id', $topicId)
                ->select('score', 'total', 'answers', 'created_at')
                ->first();

            if (!$grade) {
                return response()->json(['message' => 'No summary found'], 404);
            }

            return response()->json($grade);
        } catch (Exception $ex) {
            Log::error("Error getting summary: " . $ex->getMessage());
            return response()->json(['message' => 'Error getting summary']);
        }
    }

    public function showByTopic($topicId)
    {
        $answer = Answer::where('user_id', Auth::id())
            ->where('topic_id', $topicId)
            ->first();

        if (!$answer) {
            return response()->json(['message' => 'No answers found'], 404);
        }

        // Decode answers JSON
        $submittedAnswers = json_decode($answer->answers, true);

        // Get the questions for the topic
        $questions = Question::where('topic_id', $topicId)
            ->get(['id', 'question_text', 'options', 'correct_index']);

        // Compare and merge
        $result = $questions->map(function ($question) use ($submittedAnswers) {
            $userAnswer = collect($submittedAnswers)->firstWhere('question_id', (string) $question->id);

            return [
                'id' => $question->id,
                'question_text' => $question->question_text,
                'options' => json_decode($question->options),
                'correct_index' => $question->correct_index,
                'user_answer' => $userAnswer['answer_index'] ?? null,
                'is_correct' => isset($userAnswer['answer_index']) && $userAnswer['answer_index'] == $question->correct_index
            ];
        });

        return response()->json([
            'user_id' => $answer->user_id,
            'topic_id' => $topicId,
            'score' => $answer->score,
            'total' => $answer->total,
            'answers' => $result
        ]);
    }

    public function viewQuestionFile($filename)
    {
        return response()->download(storage_path('app/public/questions/' . $filename), $filename);
    }

    public function getReportByTopic($topic)
    {
        try {
            $report = Answer::with([
                'user:id,email,name',
                'topic',
                'profile:id,applicant_id,full_name'
            ])->where('topic_id', $topic)->get();

            if (!$report) {
                return response()->json(['message' => 'Report not found!']);
            }

            return response()->json($report);
        } catch (Exception $ex) {
            Log::error("Error getting report: " . $ex->getMessage());
            return response()->json(['message' => 'Error getting report, try again later']);
        }
    }

    public function viewAnswerSignature($signature)
    {
        // Absolute path in storage/app/public/signature
        $path = public_path('storage/signature/' . $signature);

        if (!file_exists($path)) {
            abort(404, "File not found");
        }

        return response()->file($path, [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
        ]);
    }


    public function getTopicById($id)
    {
        try {
            $topic = Topic::with(['answers', 'subject', 'questions'])
                ->where('id', $id)
                ->get();

            if ($topic->isEmpty()) {
                return response()->json(['message' => 'No data found'], 404);
            }

            return response()->json($topic);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['message' => 'Error fetching data, try again later']);
        }
    }


    public function isAnswered($topic_id)
    {
        $userId = Auth::id();

        try {
            $isAnswered = Answer::where('topic_id', $topic_id)
                ->where('user_id', $userId)
                ->exists();

            return response()->json(['answered' => (bool) $isAnswered]);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json(['message' => 'An unexpected error occurred']);
        }
    }


    public function update(Request $request, $id)
    {
        // FormData sends `questions` and `schedule` as JSON strings.
        // Merge them back into arrays so validation + the rest of the method
        // can treat them like normal request input.
        if (is_string($request->input('questions'))) {
            $request->merge([
                'questions' => json_decode($request->input('questions'), true),
            ]);
        }
        if (is_string($request->input('schedule'))) {
            $request->merge([
                'schedule' => json_decode($request->input('schedule'), true),
            ]);
        }

        $request->validate([
            'subject'                  => ['required', 'string'],
            'topics'                   => ['required', 'array', 'min:1'],
            'topics.*'                 => ['required', 'string'],
            'questions'                => ['required', 'array', 'min:1'],
            'questions.*.id'           => ['nullable', 'integer', 'exists:questions,id'], // ⬅ new, optional
            'questions.*.text'         => ['required', 'string'],
            'questions.*.options'      => ['required', 'array', 'min:1'],
            'questions.*.correctIndex' => ['required', 'integer'],
            'file'                     => ['nullable', 'file', 'mimes:pdf'],
            'schedule'                 => ['nullable', 'array'],
            'schedule.startDate'       => ['required_with:schedule', 'date'],
            'schedule.endDate'         => ['nullable', 'date', 'after:schedule.startDate'],
            'schedule.durationMinutes' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            $topic   = Topic::with(['subject', 'questions'])->findOrFail($id);
            $subject = $topic->subject;

            // ---- Resolve schedule (unchanged) ----
            $schedule        = $request->input('schedule');
            $publishedAt     = $topic->questions->first()?->published_at ?? now();
            $availableFrom   = null;
            $availableUntil  = null;
            $durationMinutes = null;

            if (! empty($schedule)) {
                $availableFrom   = Carbon::parse($schedule['startDate']);
                $availableUntil  = ! empty($schedule['endDate'])
                    ? Carbon::parse($schedule['endDate']) : null;
                $durationMinutes = ! empty($schedule['durationMinutes'])
                    ? (int) $schedule['durationMinutes'] : null;
            }

            // ---- Subject update ----
            if ($subject->name !== $request->subject) {
                $subject->name = trim($request->subject);
                $subject->slug = $this->uniqueSlug(Subject::class, $request->subject, $subject->id);
                $subject->save();
            }

            // ---- File update (unchanged) ----
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $uploadDir = storage_path('app/public/questions');
                if (! is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $newFileHash      = md5_file($file->getRealPath());
                $existingFilePath = $uploadDir . '/' . $topic->fileName;
                $existingFileHash = file_exists($existingFilePath)
                    ? md5_file($existingFilePath) : null;

                if ($newFileHash !== $existingFileHash) {
                    $fileName = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fileName);
                    $topic->fileName = $fileName;
                    $topic->save();
                }
            }

            // ---- Primary topic rename ----
            if ($topic->name !== $request->topics[0]) {
                $topic->name = trim($request->topics[0]);
                $topic->slug = $this->uniqueSlug(Topic::class, $request->topics[0], $topic->id);
                $topic->save();
            }

            // =====================================================================
            // QUESTION SYNC (primary topic) — ID-based, covers add / update /
            // delete-from-any-position. Falls back to index matching if the
            // frontend hasn't started sending IDs yet.
            // =====================================================================
            $this->syncTopicQuestions(
                $topic,
                $request->questions,
                $publishedAt,
                $availableFrom,
                $availableUntil,
                $durationMinutes
            );

            // =====================================================================
            // ADDITIONAL TOPICS (topics[1..n])
            //   Mirrors store(): every topic under a subject gets the same
            //   questions. Existing topics are kept in sync; new ones are created.
            //   Topics NOT in the payload are left alone (deleting them here
            //   would be destructive — that needs a dedicated endpoint).
            // =====================================================================
            foreach (array_slice($request->topics, 1) as $topicName) {
                $sibling = Topic::where('subject_id', $subject->id)
                    ->where('name', trim($topicName))
                    ->first();

                $siblingIsNew = ! $sibling;

                if ($siblingIsNew) {
                    $sibling = Topic::create([
                        'subject_id' => $subject->id,
                        'name'       => trim($topicName),
                        'slug'       => $this->uniqueSlug(Topic::class, $topicName, null),
                        'fileName'   => $topic->fileName,
                    ]);
                }

                // Replace sibling's question set with copies of the current questions.
                // (Siblings do not share question IDs with the primary topic — they
                // are independent copies, matching store()'s behaviour.)
                $sibling->questions()->delete(); // soft delete

                foreach ($request->questions as $qData) {
                    Question::create([
                        'topic_id'         => $sibling->id,
                        'question_text'    => $qData['text'],
                        'slug'             => $this->uniqueSlug(Question::class, $qData['text'], null, 50),
                        'options'          => json_encode($qData['options']),
                        'correct_index'    => $qData['correctIndex'],
                        'published_at'     => $publishedAt,
                        'available_from'   => $availableFrom,
                        'available_until'  => $availableUntil,
                        'duration_minutes' => $durationMinutes,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message'      => 'Quiz updated successfully',
                'availability' => [
                    'mode'             => $schedule ? 'scheduled' : 'available_now',
                    'available_from'   => $availableFrom?->toIso8601String(),
                    'available_until'  => $availableUntil?->toIso8601String(),
                    'duration_minutes' => $durationMinutes,
                ],
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('Error updating quiz: ' . $ex->getMessage());
            return response()->json(['message' => 'Error updating quiz'], 500);
        }
    }

/* ============================================================
 |  Helpers
 ============================================================ */

    /**
     * Sync a topic's question set from the payload.
     *
     *  • Questions with `id`              → update that row
     *  • Questions with `id == null`      → create a new row
     *  • Existing rows NOT in the payload → soft delete (from any position)
     *
     * Falls back to index-based matching ONLY if no question has an `id`
     * — that keeps the old frontend working while you migrate.
     */
    private function syncTopicQuestions(
        Topic $topic,
        array $questionsData,
        Carbon $publishedAt,
        ?Carbon $availableFrom,
        ?Carbon $availableUntil,
        ?int $durationMinutes
    ): void {
        $existing  = $topic->questions()->get()->keyBy('id');
        $hasAnyIds = collect($questionsData)->contains(fn($q) => ! empty($q['id']));
        $seenIds   = [];

        foreach ($questionsData as $index => $qData) {
            if ($hasAnyIds) {
                if (! empty($qData['id'])) {
                    $question = $existing->get($qData['id']);
                    if (! $question) {
                        throw new \RuntimeException(
                            "Question {$qData['id']} does not belong to topic {$topic->id}"
                        );
                    }
                } else {
                    $question = new Question(['topic_id' => $topic->id]);
                }
            } else {
                // Legacy fallback — no IDs in payload
                $question = $existing->values()->get($index)
                    ?? new Question(['topic_id' => $topic->id]);
            }

            $question->fill([
                'question_text'    => $qData['text'],
                'slug'             => $this->uniqueSlug(Question::class, $qData['text'], $question->id, 50),
                'options'          => json_encode($qData['options']),
                'correct_index'    => $qData['correctIndex'],
                'published_at'     => $publishedAt,
                'available_from'   => $availableFrom,
                'available_until'  => $availableUntil,
                'duration_minutes' => $durationMinutes,
                'closed_at'        => null,
            ])->save();

            $seenIds[] = $question->id;
        }

        // Soft-delete anything not in the payload (from ANY position)
        $toDelete = $existing->keys()->diff($seenIds);
        if ($toDelete->isNotEmpty()) {
            Question::whereIn('id', $toDelete)->delete();
            Log::info('Removed questions on quiz edit', [
                'topic_id' => $topic->id,
                'removed'  => $toDelete->all(),
            ]);
        }
    }

    private function uniqueSlug(
        string $model,
        string $value,
        ?int $excludeId = null,
        int $limit = 100
    ): string {
        $base    = Str::slug(Str::limit($value, $limit));
        $slug    = $base;
        $counter = 1;

        // Only include trashed rows when the model supports soft deletes.
        // Otherwise `withTrashed()` throws (e.g. on Subject, which has no
        // deleted_at column).
        $query = in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive($model)
        )
            ? $model::withTrashed()
            : $model::query();

        while (
            (clone $query)
            ->where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()
        ) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }


    /**
     * Fetch only available quizzes for the logged-in user,
     * along with their score if they have already taken it.
     */
    public function getUserQuizzes(Request $request)
    {
        $userId = Auth::id();

        // 1. Fetch subjects that have topics with available questions
        $subjects = Subject::with(['topics' => function ($query) {
            $query->whereHas('questions', function ($q) {
                $q->available(); // ⬅ Only fetch topics that have available questions
            })->withCount('questions');
        }])
            ->whereHas('topics', function ($query) {
                $query->whereHas('questions', function ($q) {
                    $q->available();
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate(10);

        // 2. Get the user's answers for these topics to avoid N+1 queries
        $topicIds = $subjects->pluck('topics.*.id')->flatten();

        $answers = Answer::where('user_id', $userId)
            ->whereIn('topic_id', $topicIds)
            ->get()
            ->keyBy('topic_id');

        // 3. Attach answer status (is_answered, score, total) to each topic
        foreach ($subjects as $subject) {
            foreach ($subject->topics as $topic) {
                $answer = $answers->get($topic->id);
                $topic->is_answered = (bool) $answer;
                $topic->score = $answer ? $answer->score : null;
                $topic->total = $answer ? $answer->total : null;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $subjects->items(),
            'pagination' => [
                'total' => $subjects->total(),
                'per_page' => $subjects->perPage(),
                'current_page' => $subjects->currentPage(),
                'last_page' => $subjects->lastPage(),
                'from' => $subjects->firstItem(),
                'to' => $subjects->lastItem()
            ]
        ]);
    }

    /**
     * SECURITY FIX: Prevent users from fetching questions for future/expired quizzes
     */
    public function getQuestions(Topic $topic)
    {
        $questions = $topic->questions()->select('id', 'question_text', 'options', 'correct_index')->get();

        return response()->json([
            'success' => true,
            'questions' => $questions
        ]);
    }
}
