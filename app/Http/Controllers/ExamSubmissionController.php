<?php

namespace App\Http\Controllers;

use App\Models\ExamBatch;
use App\Models\ExamBatchUser;
use Illuminate\Http\Request;
use App\Models\ExamSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamSubmissionController extends Controller
{
    public function index()
    {
        $data = ExamSubmission::where('user_id', auth()->id())->with('exam')->first();

        $examBatchUser = ExamBatchUser::where('user_id', auth()->id())->first();
        
        $data['isProgrammer'] = $examBatchUser && str_contains($examBatchUser->exam->title, 'Programmer');

        return apiResponse($data, 'success in obtaining submissions', true, 200);
    }

    public function start(Request $request)
    {
        // ✅ Cek: hanya user role 'user' yang boleh ikut ujian
        if (auth()->user()->role !== 'user') {
            return apiResponse(null, 'only participants (users) may take the exam.', false, 403);
        }
        
        $request->validate([
            'exam_id'       => 'required|exists:exams,id',
        ]);

        $user = auth()->user();
        
        // Update Exam Batch

        $batchUser = ExamBatchUser::with('examBatch')->where('user_id', $user->id)->first();

        if (!$batchUser) {
            return apiResponse(null, 'user are not registered in any batch.', false, 403);
        }

        // ✅ Cek user terdaftar di batch
        if ($batchUser->user_id !== $user->id) {
            return apiResponse(null, 'user are not registered in this batch.', false, 403);
        }

        $examBatch = ExamBatch::where('id', $batchUser->exam_batch_id)->first();

        if (!$examBatch) {
            return apiResponse(null, 'batch not found.', false, 404);
        }

        // // ✅ Pastikan batch cocok dengan exam
        // if ($examBatch->exam_id !== $request->exam_id) {
        //     return apiResponse(null, 'the batch does not comply with the test.', false, 400);
        // }

        // ✅ Validasi waktu sesi
        $now = now();
        if ($now->lt($examBatch->start_time)) {
            return apiResponse(null, 'exam is not yet started.', false, 403);
        }
        if ($now->gt($examBatch->end_time)) {
            return apiResponse(null, 'exam has already ended.', false, 403);
        }

        // // ✅ Cek apakah user sudah pernah ikut
        // $existing = ExamSubmission::where('exam_id', $request->exam_id)
        //     ->where('user_id', $user->id)
        //     ->first();

        // if ($existing) {
        //     return apiResponse(null, 'user have already taken the exam.', false, 409);
        // }

        try {
            DB::beginTransaction();

            // ✅ Update exam_id pada exam_batch (jika belum ada / perlu diupdate)
            $batchUser->exam_id = $request->exam_id;
            $batchUser->save();

            // ✅ Buat submission
            $submission = ExamSubmission::create([
                'exam_id'       => $request->exam_id,
                'exam_batch_id' => $examBatch->id,
                'user_id'       => $user->id,
                'started_at'    => $now,
            ]);

            DB::commit();

            return apiResponse($submission, 'Exam started successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to start exam: ' . $e->getMessage());
            return apiResponse(null, 'Failed to start exam.', false, 500);
        }
    }

    public function submit()
    {
        try {
            $submission = ExamSubmission::where('user_id', auth()->id())->first();

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
