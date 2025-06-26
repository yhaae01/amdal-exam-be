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
            return response()->json([
                'message' => 'Jawaban berhasil disimpan.',
                'data' => $answer
            ], 201);

        } catch (\Exception $e) {
            Log::error('Gagal simpan jawaban: ' . $e->getMessage());

            return response()->json([
                'message' => 'Gagal menyimpan jawaban.',
                'error' => $e->getMessage()
            ], 500);
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
    
            return response()->json([
                'message' => 'Jawaban berhasil diperbarui.',
                'data' => $answer
            ]);
    
        } catch (\Exception $e) {
            Log::error('Gagal update jawaban: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'Gagal update jawaban.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Answer $answer)
    {
        $this->authorizeAnswerOwner($answer);

        return response()->json([
            'data' => $answer->load(['question', 'selectedOption'])
        ]);
    }

    private function authorizeAnswerOwner(Answer $answer)
    {
        if ($answer->exam_submission->user_id !== auth()->id()) {
            abort(403, 'Forbidden');
        }
    }
}
