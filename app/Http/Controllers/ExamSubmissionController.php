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
        $data = ExamSubmission::where('user_id', auth()->id())->with('exam')->get();

        return apiResponse($data, 'success in obtaining submissions', true, 200);
    }

    public function start(Request $request)
    {
        try {
            // ✅ Cek: hanya user role 'user' yang boleh ikut ujian
            if (auth()->user()->role !== 'user') {
                return apiResponse(null, 'only participants (users) may take the exam.', false, 403);
            }
            
            $request->validate([
                'exam_id'       => 'required|exists:exams,id',
                'exam_batch_id' => 'required|exists:exam_batches,id',
            ]);
    
            $user = auth()->user();
    
            $batch = ExamBatch::findOrFail($request->exam_batch_id);
    
            // ✅ Pastikan batch cocok dengan exam
            if ($batch->exam_id !== $request->exam_id) {
                return apiResponse(null, 'the batch does not comply with the test.', false, 400);
            }
    
            // ✅ Cek user terdaftar di batch
            if (!$user->examBatches->contains($batch->id)) {
                return apiResponse(null, 'user are not registered in this batch.', false, 403);
            }
    
            // ✅ Validasi waktu sesi
            $now = now();
            if ($now->lt($batch->start_time)) {
                return apiResponse(null, 'exam is not yet started.', false, 403);
            }
            if ($now->gt($batch->end_time)) {
                return apiResponse(null, 'exam has already ended.', false, 403);
            }
    
            // ✅ Cek apakah user sudah pernah ikut
            $existing = ExamSubmission::where('exam_id', $request->exam_id)
                ->where('user_id', $user->id)
                ->first();
    
            if ($existing) {
                return apiResponse(null, 'user have already taken the exam.', false, 409);
            }
    
            // ✅ Buat submission
            $submission = ExamSubmission::create([
                'exam_id'       => $request->exam_id,
                'exam_batch_id' => $batch->id,
                'user_id'       => $user->id,
                'started_at'    => $now,
            ]);
    
            return apiResponse($submission, 'success in starting the exam', true, 201);
        } catch (\Exception $e) {
            Log::error('failed to start the exam: ' . $e->getMessage());

            return apiResponse(null, 'failed to start the exam.', false, 500);
        }
    }

    public function submit(Request $request, ExamSubmission $submission)
    {
        try {
            if ($submission->user_id !== auth()->id()) {
                return apiResponse(null, 'unauthorized', false, 403);
            }
    
            if ($submission->submitted_at) {
                return apiResponse(null, 'user has already submitted previously.', false, 400);
            }
    
            $submission->submitted_at = now();
    
            $score = $this->calculateScore($submission);
            $submission->score = $score;
            $submission->save();
    
            $data = [
                'score' => $score
            ];

            return apiResponse($data, 'success in submitting the exam', true, 200);
        } catch (\Exception $e) {
            Log::error('failed to submit the exam: ' . $e->getMessage());
            
            return apiResponse(null, 'an error occurred while submitting the exam.', false, 500);
        }
    }

    public function show(ExamSubmission $submission)
    {
        try {
            if ($submission->user_id !== auth()->id()) {
                return apiResponse(null, 'unauthorized', false, 403);
            }
    
            return apiResponse($submission->load('exam'), 'success in obtaining submission', true, 200);
        } catch (\Exception $e) {
            Log::error('failed to display submission: ' . $e->getMessage());

            return apiResponse(null, 'failed to display submission.', false, 500);
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
