<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ExamSubmissionController;
use App\Http\Controllers\ExamBatchController;

Route::post('/login', [AuthController::class, 'login']);
// Get all exams with questions and user answers
Route::get('/exams/all', [ExamController::class, 'getAllExams']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // 📚 CRUD Exam (ujian)
    Route::apiResource('exams', ExamController::class);

    // ❓ CRUD Question (soal)
    Route::apiResource('questions', QuestionController::class);

    // 🔘 CRUD Option (pilihan jawaban)
    Route::apiResource('options', OptionController::class);

    // 📝 Pelaksanaan ujian
    Route::post('/exam-submissions/start', [ExamSubmissionController::class, 'start']); // Mulai ujian
    Route::post('/exam-submissions/{submission}/submit', [ExamSubmissionController::class, 'submit']); // Submit ujian
    Route::get('/exam-submissions/{submission}', [ExamSubmissionController::class, 'show']); // Lihat detail ujian yang sudah dikerjakan
    Route::get('/my-submissions', [ExamSubmissionController::class, 'index']); // Daftar semua submission user

    // ✏️ Jawaban user
    Route::apiResource('answers', AnswerController::class)->only([
        'store', 'update', 'show'
    ]);

    // 🗓️ Manajemen Batch Ujian
    Route::get('/exam-batches', [ExamBatchController::class, 'index']);
    Route::post('/exam-batches', [ExamBatchController::class, 'store']);
    Route::get('/exam-batches/{id}', [ExamBatchController::class, 'show']);
    Route::delete('/exam-batches/{id}', [ExamBatchController::class, 'destroy']);
    Route::post('/exam-batches/{id}/assign-users', [ExamBatchController::class, 'assignUsers']);
});