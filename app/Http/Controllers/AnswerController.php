<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AnswerController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'exam_submission_id' => 'required|exists:exam_submissions,id',
                'question_id'        => 'required|exists:questions,id',
                'selected_option_id' => 'nullable|exists:options,id',
                'answer_text'        => 'nullable|string'
            ]);

            // Cegah orang lain simpan ke submission bukan miliknya
            $submission = \App\Models\ExamSubmission::find($validated['exam_submission_id']);
            if ($submission->user_id !== Auth::id()) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $existing = Answer::where('exam_submission_id', $validated['exam_submission_id'])
                ->where('question_id', $validated['question_id'])
                ->first();

            if ($existing) {
                $existing->update([
                    'selected_option_id' => $validated['selected_option_id'] ?? null,
                    'answer_text'        => $validated['answer_text'] ?? null,
                ]);

                return response()->json($existing);
            }

            $answer = Answer::create($validated);
            return apiResponse($answer, 'answer saved successfully', true, 201);
        } catch (\Exception $e) {
            Log::error('Failed to save answer: ' . $e->getMessage());

            return apiResponse(null, 'failed to save answer', false, 500);
        }
    }

    public function update(Request $request, Answer $answer)
    {
        try {
            $this->authorizeAnswerOwner($answer);
    
            $validated = $request->validate([
                'selected_option_id' => 'nullable|exists:options,id',
                'answer_text'        => 'nullable|string'
            ]);
    
            $answer->update($validated);
    
            return apiResponse($answer, 'answer updated successfully', true, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update answer: ' . $e->getMessage());
    
            return apiResponse(null, 'failed to update answer', false, 500);
        }
    }

    public function show(Answer $answer)
    {
        $this->authorizeAnswerOwner($answer);

        $data = $answer->load(['question', 'selectedOption']);

        return apiResponse($data, 'answer successfully found', true, 200);
    }

    private function authorizeAnswerOwner(Answer $answer)
    {
        if ($answer->exam_submission->user_id !== auth()->id()) {
            abort(403, 'Forbidden');
        }
    }
}
