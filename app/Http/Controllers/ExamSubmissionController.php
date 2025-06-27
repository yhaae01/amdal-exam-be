<?php

namespace App\Http\Controllers;

use App\Models\ExamBatch;
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
            // ✅ Cek: hanya user role 'user' yang boleh ikut ujian
            if (auth()->user()->role !== 'user') {
                return response()->json([
                    'message' => 'Hanya peserta (user) yang boleh mengikuti ujian.'
                ], 403);
            }
            
            $request->validate([
                'exam_id'       => 'required|exists:exams,id',
                'exam_batch_id' => 'required|exists:exam_batches,id',
            ]);
    
            $user = auth()->user();
    
            $batch = ExamBatch::findOrFail($request->exam_batch_id);
    
            // ✅ Pastikan batch cocok dengan exam
            if ($batch->exam_id !== $request->exam_id) {
                return response()->json([
                    'message' => 'Batch tidak sesuai dengan ujian.'
                ], 400);
            }
    
            // ✅ Cek user terdaftar di batch
            if (!$user->examBatches->contains($batch->id)) {
                return response()->json([
                    'message' => 'Kamu tidak terdaftar di batch ini.'
                ], 403);
            }
    
            // ✅ Validasi waktu sesi
            $now = now();
            if ($now->lt($batch->start_time)) {
                return response()->json([
                    'message' => 'Belum waktunya ujian dimulai.'
                ], 403);
            }
            if ($now->gt($batch->end_time)) {
                return response()->json([
                    'message' => 'Waktu ujian pada batch ini telah berakhir.'
                ], 403);
            }
    
            // ✅ Cek apakah user sudah pernah ikut
            $existing = ExamSubmission::where('exam_id', $request->exam_id)
                ->where('user_id', $user->id)
                ->first();
    
            if ($existing) {
                return response()->json([
                    'message' => 'Ujian sudah pernah dikerjakan.'
                ], 409);
            }
    
            // ✅ Buat submission
            $submission = ExamSubmission::create([
                'exam_id'       => $request->exam_id,
                'exam_batch_id' => $batch->id,
                'user_id'       => $user->id,
                'started_at'    => $now,
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
