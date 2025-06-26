<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ExamSubmissionController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // 📚 CRUD Exam (ujian)
    Route::apiResource('exams', ExamController::class);

    // ❓ CRUD Question (soal)
    Route::apiResource('questions', QuestionController::class);

    // 🔘 CRUD Option (pilihan jawaban)
    Route::apiResource('options', OptionController::class);

    // 📝 Submit ujian
    Route::post('/exam-submissions/start', [ExamSubmissionController::class, 'start']); // Mulai ujian
    Route::post('/exam-submissions/{submission}/submit', [ExamSubmissionController::class, 'submit']); // Submit ujian
    Route::get('/exam-submissions/{submission}', [ExamSubmissionController::class, 'show']); // Lihat detail ujian yang sudah dikerjakan
    Route::get('/my-submissions', [ExamSubmissionController::class, 'index']); // Daftar semua submission user

    // ✏️ Jawaban user (simpan/update satu soal)
    Route::apiResource('answers', AnswerController::class)->only([
        'store', // Simpan atau update jawaban (jika sudah pernah dijawab)
        'update', // Update jawaban
        'show'    // Lihat jawaban
    ]);
});