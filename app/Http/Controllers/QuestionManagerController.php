<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuestionManagerController extends Controller
{
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
            $subject = Subject::create([
                'name' => trim($request->subject)
            ]);

            foreach ($request->topics as $topicName) {
                $topic = Topic::create([
                    'subject_id' => $subject->id,
                    'name' => $topicName
                ]);

                foreach ($request->questions as $questionData) {
                    Question::create([
                        'topic_id' => $topic->id,
                        'question_text' => $questionData['text'],
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
