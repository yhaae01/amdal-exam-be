<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExamSubmission;
use Illuminate\Support\Facades\Log;

class ExamSubmissionController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => ExamSubmission::where('user_id', auth()->id())->with('exam')->get()
        ]);
    }

    public function start(Request $request)
    {
        try {
            $request->validate([
                'exam_id' => 'required|exists:exams,id'
            ]);
    
            $existing = ExamSubmission::where('exam_id', $request->exam_id)
                ->where('user_id', auth()->id())
                ->first();
    
            if ($existing) {
                return response()->json(['message' => 'Ujian sudah pernah dikerjakan.'], 409);
            }
    
            $submission = ExamSubmission::create([
                'exam_id'   => $request->exam_id,
                'user_id'   => auth()->id(),
                'started_at' => now(),
            ]);
    
            return response()->json($submission, 201);
    
        } catch (\Exception $e) {
            Log::error('Gagal memulai ujian: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat memulai ujian.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function submit(Request $request, ExamSubmission $submission)
    {
        try {
            if ($submission->user_id !== auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
    
            if ($submission->submitted_at) {
                return response()->json(['message' => 'Sudah disubmit sebelumnya.'], 400);
            }
    
            $submission->submitted_at = now();
    
            $score = $this->calculateScore($submission);
            $submission->score = $score;
            $submission->save();
    
            return response()->json([
                'message' => 'Ujian disubmit.',
                'score'   => $score
            ]);
    
        } catch (\Exception $e) {
            Log::error('Gagal submit ujian: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat submit ujian.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show(ExamSubmission $submission)
    {
        try {
            if ($submission->user_id !== auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
    
            return response()->json([
                'data' => $submission->load('exam')
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menampilkan submission: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal mengambil data submission.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    private function calculateScore(ExamSubmission $submission)
    {
        $submission->load('answers.selectedOption');

        $correct = $submission->answers->filter(function ($answer) {
            return optional($answer->selectedOption)->is_correct;
        })->count();

        $total = $submission->answers->count();

        return $total > 0 ? round(($correct / $total) * 100, 2) : 0;
    }
}
