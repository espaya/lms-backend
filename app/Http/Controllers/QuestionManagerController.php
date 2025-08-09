<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

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
        ]);


        try {
            DB::beginTransaction();

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

                $topic = Topic::create([
                    'subject_id' => $subject->id,
                    'name' => $topicName,
                    'slug' => $topicSlug
                ]);

                foreach ($request->questions as $questionData) {
                    // Generate base slug from question text (trim to 50 chars to keep slug manageable)
                    $baseQuestionSlug = Str::slug(Str::limit($questionData['text'], 50));
                    $questionSlug = $baseQuestionSlug;
                    $questionCounter = 1;

                    // Ensure the question slug is unique
                    while (Question::where('slug', $questionSlug)->exists()) {
                        $questionSlug = $baseQuestionSlug . '-' . $questionCounter++;
                    }

                    Question::create([
                        'topic_id' => $topic->id,
                        'question_text' => $questionData['text'],
                        'slug' => $questionSlug,
                        'options' => json_encode($questionData['options']),
                        'correct_index' => $questionData['correctIndex']
                    ]);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Questions uploaded successfully']);
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('Error uploading questions: ' . $ex->getMessage());
            return response()->json(['message' => 'Error uploading questions. Try again later'], 500);
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
}
