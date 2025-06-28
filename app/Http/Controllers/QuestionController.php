<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return apiResponse(null, 'forbidden', false, 403);
        }
    
        $search = $request->query('search');
    
        $query = Question::with('options')->orderBy('order');
    
        if ($search) {
            $query->where('question_text', 'ilike', '%' . $search . '%')
                ->orWhere('question_type', 'ilike', '%' . $search . '%');
        }
    
        $questions = $query->paginate(10);
    
        return apiResponse($questions, 'success in obtaining questions', true, 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'exam_id'       => 'required|exists:exams,id',
                'question_text' => 'required|string',
                'image'         => 'nullable|file|image|max:2048',
                'order'         => 'nullable|integer',
                'question_type' => 'in:multiple_choice,essay',
                'weight'        => 'nullable|numeric'
            ]);
    
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('questions', 'public');
                $validated['image'] = $path;
            }
    
            $question = Question::create($validated);
    
            return apiResponse($question, 'success in creating question', true, 201);
        } catch (\Exception $e) {
            Log::error('failed to save question: ' . $e->getMessage());
            return apiResponse(null, 'failed to save question.', false, 500);
        }
    }

    public function storeBatch(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return apiResponse(null, 'forbidden', false, 403);
        }

        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'exam_id'                           => 'required|exists:exams,id',
                'questions'                         => 'required|array|min:1',
                'questions.*.question_text'         => 'required|string',
                'questions.*.question_type'         => 'in:multiple_choice,essay',
                'questions.*.weight'                => 'nullable|numeric',
                'questions.*.options'               => 'required|array|min:1',
                'questions.*.options.*.option_text' => 'required|string',
                'questions.*.options.*.is_correct'  => 'boolean',
            ]);

            foreach ($validated['questions'] as $qData) {
                $qData['exam_id'] = $validated['exam_id'];
                $options = $qData['options'];
                unset($qData['options']);

                $question = Question::create($qData);

                foreach ($options as $option) {
                    $option['question_id'] = $question->id;
                    Option::create($option);
                }
            }

            DB::commit();
            return apiResponse(null, 'All questions and options saved successfully.', true, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to batch store questions: ' . $e->getMessage());
            return apiResponse(null, 'Failed to save questions.', false, 500);
        }
    }

    public function show(Question $question)
    {
        try {
            $question->load(['options', 'answers', 'exam']);
            return apiResponse($question, 'success in obtaining question', true, 200);
        } catch (\Exception $e) {
            Log::error('failed to retrieve question data: ' . $e->getMessage());
    
            return apiResponse(null, 'failed to retrieve question data.', false, 500);
        }
    }

    public function update(Request $request, Question $question)
    {
        try {
            $validated = $request->validate([
                'question_text' => 'sometimes|required|string',
                'image'         => 'nullable|file|image|max:2048',
                'order'         => 'nullable|integer',
                'question_type' => 'in:multiple_choice,essay',
                'weight'        => 'nullable|numeric'
            ]);
    
            if ($request->hasFile('image')) {
                if ($question->image && Storage::disk('public')->exists($question->image)) {
                    Storage::disk('public')->delete($question->image);
                }
    
                $validated['image'] = $request->file('image')->store('questions', 'public');
            }
    
            $question->update($validated);
    
            return apiResponse($question, 'success in updating question', true, 200);
        } catch (\Exception $e) {
            Log::error('failed to update questions: ' . $e->getMessage());

            return apiResponse(null, 'failed to update questions.', false, 500);
        }
    }

    public function destroy(Question $question)
    {
        try {
            if ($question->image && Storage::disk('public')->exists($question->image)) {
                Storage::disk('public')->delete($question->image);
            }
    
            $question->delete();
    
            return apiResponse(null, 'success in deleting question', true, 200);    
        } catch (\Exception $e) {
            Log::error('failed to delete question: ' . $e->getMessage());

            return apiResponse(null, 'failed to delete question.', false, 500);
        }
    }

    public function listQuestions($exam_id) {
        try {
            $questions = Question::where('exam_id', $exam_id)->orderBy('order');

            if (auth()->user()->role === 'admin') {
                $questions = $questions->paginate(10);
            } else {
                $questions = $questions->with(['options:id,question_id,option_text'])->get();
            }
            return apiResponse($questions, 'success in obtaining questions', true, 200);
        } catch (\Exception $e) {
            Log::error('failed to retrieve question data: ' . $e->getMessage());
    
            return apiResponse(null, 'failed to retrieve question data.', false, 500);
        }
    }
}
