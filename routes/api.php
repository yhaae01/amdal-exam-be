<?php

use App\Http\Controllers\ActivityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ExamBatchController;
use App\Http\Controllers\EmailBlastController;
use App\Http\Controllers\ExamSubmissionController;

Route::post('/login', [AuthController::class, 'login']);
Route::get('/get-users', [UserController::class, 'index']);

// Alternate
Route::get('/get-qualified', [UserController::class, 'getQualified']);

Route::middleware(['auth:api'])->group(function () {
    // Get all exams with questions and user answers
    Route::get('/exams/all', [ExamController::class, 'getAllExams']);
    Route::get('/exams/all/without-paginate', [ExamController::class, 'getAllExamsWithoutPaginate']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // 📚 CRUD Exam (ujian)
    Route::apiResource('exams', ExamController::class);

    // ❓ CRUD Question (soal)
    Route::apiResource('questions', QuestionController::class);
    Route::get('/questions/list/{exam_id}', [QuestionController::class, 'listQuestions']);
    Route::post('/questions/store-batch', [QuestionController::class, 'storeBatch']);

    // 🔘 CRUD Option (pilihan jawaban)
    Route::apiResource('options', OptionController::class);
    Route::get('/options/list/{question_id}', [OptionController::class, 'listOptions']);

    // 📝 Pelaksanaan ujian
    Route::post('/exam-submissions/start', [ExamSubmissionController::class, 'start']); // Mulai ujian
    Route::post('/exam-submissions/submit', [ExamSubmissionController::class, 'submit']); // Submit ujian
    Route::get('/exam-submissions/{submission}', [ExamSubmissionController::class, 'show']); // Lihat detail ujian yang sudah dikerjakan
    Route::get('/my-submissions', [ExamSubmissionController::class, 'index']); // Daftar semua submission user

    // ✏️ Jawaban user
    Route::get('/answers/list', [AnswerController::class, 'getAllAnswerUsers']);
    Route::apiResource('answers', AnswerController::class)->only([
        'index', 'store', 'update', 'show'
    ]);
    Route::get('/exam-batches/all', [ExamBatchController::class, 'all']);
    Route::get('/exam-batches', [ExamBatchController::class, 'index']);
    Route::get('/exam-batches/{id}', [ExamBatchController::class, 'show']);

    // Activity User
    Route::post('/activity', [ActivityController::class, 'addActivity']);
    Route::get('/count-activity', [ActivityController::class, 'checkCountActivity']);

    Route::group(['middleware' => ['is.admin']], function () {

        Route::get('/dashboard-highlight', [DashboardController::class, 'index']);
        Route::get('/top-score', [DashboardController::class, 'top_score_exam']);
        Route::get('/batch-highlight', [DashboardController::class, 'current_batch_list']);

        // 🗓️ Manajemen Batch Ujian
        Route::post('/exam-batches', [ExamBatchController::class, 'store']);
        Route::delete('/exam-batches/{id}', [ExamBatchController::class, 'destroy']);
        Route::post('/exam-batches/{id}/assign-users', [ExamBatchController::class, 'assignUsers']);

        Route::get('/result-qualified', [UserController::class, 'result_qualified']);
        Route::get('/export-result-qualified', [UserController::class, 'export_result_qualified']);

        // 👤 Manajemen User
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/not-assign-batch', [UserController::class, 'user_not_assign_batch']);
        Route::get('/users/not-submitted-yet', [UserController::class, 'user_not_submitted_yet']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::post('/users/import', [UserController::class, 'import']); 
        
        Route::post('/blast-email', [EmailBlastController::class, 'blastEmail']); 
    });
});