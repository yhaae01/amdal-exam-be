<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return apiResponse(null, 'forbidden', false, 403);
        }

        $questions = Question::with('options')->orderBy('order')->paginate(10);

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

    public function show(Question $question)
    {
        try {
            $question->load(['options', 'answers']);
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
}
