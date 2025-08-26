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
        $request->validate([
            'subject' => ['required', 'string'],
            'topics' => ['required', 'array', 'min:1'],
            'topics.*' => ['required', 'string'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.text' => ['required', 'string'],
            'questions.*.options' => ['required', 'array', 'min:1'],
            'questions.*.correctIndex' => ['required', 'integer'],
            'file' => ['required', 'file', 'mimes:pdf']
        ]);

        $uploadedFilePath = null;

        try {
            DB::beginTransaction();

            // ✅ First upload the file so we have its name
            $file = $request->file('file');
            $uploadDir = storage_path('app/public/questions');

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $uploadedFilePath = $uploadDir . '/' . $fileName;

            // Generate unique slug for subject
            $baseSlug = Str::slug($request->subject);
            $slug = $baseSlug;
            $counter = 1;

            while (Subject::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $subject = Subject::create([
                'name' => trim($request->subject),
                'slug' => $slug
            ]);

            foreach ($request->topics as $topicName) {
                // Generate unique slug for each topic
                $baseTopicSlug = Str::slug($topicName);
                $topicSlug = $baseTopicSlug;
                $topicCounter = 1;

                while (Topic::where('slug', $topicSlug)->exists()) {
                    $topicSlug = $baseTopicSlug . '-' . $topicCounter++;
                }

                // ✅ Store file name in topic
                $topic = Topic::create([
                    'subject_id' => $subject->id,
                    'name'       => $topicName,
                    'slug'       => $topicSlug,
                    'fileName'       => $fileName // <-- Added
                ]);

                foreach ($request->questions as $questionData) {
                    // Generate unique slug for question
                    $baseQuestionSlug = Str::slug(Str::limit($questionData['text'], 50));
                    $questionSlug = $baseQuestionSlug;
                    $questionCounter = 1;

                    while (Question::where('slug', $questionSlug)->exists()) {
                        $questionSlug = $baseQuestionSlug . '-' . $questionCounter++;
                    }

                    Question::create([
                        'topic_id'       => $topic->id,
                        'question_text'  => $questionData['text'],
                        'slug'           => $questionSlug,
                        'options'        => json_encode($questionData['options']),
                        'correct_index'  => $questionData['correctIndex']
                    ]);
                }
            }

            // send email to all users
            $users = User::where('role', 'USER')->get();
            $fileUrl = asset('storage/questions/' . $fileName);
            foreach ($users as $user) {
                Mail::to($user->email)->send(new QuestionUploadMail($user, $fileUrl));
            }

            DB::commit();

            return response()->json([
                'message' => 'Questions uploaded successfully',
                'file_path' => asset('storage/questions/' . $fileName)
            ]);
        } catch (Exception $ex) {
            DB::rollBack();

            if ($uploadedFilePath && file_exists($uploadedFilePath)) {
                unlink($uploadedFilePath);
            }

            Log::error('Error uploading questions: ' . $ex->getMessage());

            return response()->json([
                'message' => 'Error uploading questions. Try again later'
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


    public function getQuestions(Topic $topic)
    {
        $questions = $topic->questions()->select('id', 'question_text', 'options', 'correct_index')->get();

        return response()->json([
            'success' => true,
            'questions' => $questions
        ]);
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
        $path = storage_path('app/public/questions/' . $filename);
        if (!file_exists($path)) {
            abort(404, "File not found");
        }

        return response()->file($path);
    }

    public function getReportByTopic($topic)
    {
        try {
            $report = Answer::with(['user', 'topic'])->where('topic_id', $topic)->get();

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
        $path = storage_path('app/public/signature/' . $signature);
        if (!file_exists($path)) {
            abort(404, "File not found");
        }

        return response()->file($path);
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

    public function update(Request $request, $id)
    {
        $request->validate([
            'subject' => ['required', 'string'],
            'topics' => ['required', 'array', 'min:1'],
            'topics.*' => ['required', 'string'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.text' => ['required', 'string'],
            'questions.*.options' => ['required', 'array', 'min:1'],
            'questions.*.correctIndex' => ['required', 'integer'],
            'file' => ['nullable', 'file', 'mimes:pdf']
        ]);

        try {
            DB::beginTransaction();

            $findData = Topic::with(['subject', 'questions'])
                ->where('id', $id)
                ->first();

            if (!$findData) {
                return response()->json(['message' => 'Topic not found'], 404);
            }

            // ✅ Update Subject if changed
            if ($findData->subject->name !== $request->subject) {
                $baseSlug = Str::slug($request->subject);
                $slug = $baseSlug;
                $counter = 1;
                while (Subject::where('slug', $slug)->where('id', '!=', $findData->subject->id)->exists()) {
                    $slug = $baseSlug . '-' . $counter++;
                }

                $findData->subject->name = trim($request->subject);
                $findData->subject->slug = $slug;

                if ($findData->subject->isDirty()) {
                    $findData->subject->save();
                }
            }

            // ✅ Update file if uploaded
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $uploadDir = storage_path('app/public/questions');

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Compare using hash (safer than just filename)
                $newFileHash = md5_file($file->getRealPath());

                $existingFilePath = $uploadDir . '/' . $findData->fileName;
                $existingFileHash = file_exists($existingFilePath) ? md5_file($existingFilePath) : null;

                if ($newFileHash !== $existingFileHash) {
                    // Only upload if file content differs
                    $fileName = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fileName);

                    $findData->fileName = $fileName;
                    $findData->save();
                }
            }

            // ✅ Update topics (for simplicity: update current one)
            if ($findData->name !== $request->topics[0]) {
                $baseTopicSlug = Str::slug($request->topics[0]);
                $topicSlug = $baseTopicSlug;
                $counter = 1;
                while (Topic::where('slug', $topicSlug)->where('id', '!=', $findData->id)->exists()) {
                    $topicSlug = $baseTopicSlug . '-' . $counter++;
                }

                $findData->name = $request->topics[0];
                $findData->slug = $topicSlug;

                if ($findData->isDirty()) {
                    $findData->save();
                }
            }

            // ✅ Update questions (loop)
            foreach ($request->questions as $index => $qData) {
                $question = $findData->questions[$index] ?? null;
                if (!$question) continue;

                $question->question_text = $qData['text'];
                $question->options = json_encode($qData['options']);
                $question->correct_index = $qData['correctIndex'];

                // Slug check
                $baseQuestionSlug = Str::slug(Str::limit($qData['text'], 50));
                $slug = $baseQuestionSlug;
                $counter = 1;
                while (Question::where('slug', $slug)->where('id', '!=', $question->id)->exists()) {
                    $slug = $baseQuestionSlug . '-' . $counter++;
                }
                $question->slug = $slug;

                if ($question->isDirty()) {
                    $question->save();
                }
            }

            DB::commit();

            return response()->json(['message' => 'Data updated successfully']);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error("Error updating topic: " . $ex->getMessage());
            return response()->json(['message' => 'Error updating data'], 500);
        }
    }
}
