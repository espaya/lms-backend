<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QuestionManagerController extends Controller
{
    public function index()
    {
        $subjects = Subject::with('topics')->orderBy('id', 'DESC')->paginate(10);

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

            $topic = Topic::orderBy('id', 'DESC')
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
}
