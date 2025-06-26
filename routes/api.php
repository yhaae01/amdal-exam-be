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

    Route::apiResource('exams', ExamController::class);
    Route::apiResource('questions', QuestionController::class);
    Route::apiResource('options', OptionController::class);

    Route::post('/exam-submissions/start', [ExamSubmissionController::class, 'start']);
    Route::post('/exam-submissions/{submission}/submit', [ExamSubmissionController::class, 'submit']);
    Route::get('/exam-submissions/{submission}', [ExamSubmissionController::class, 'show']);
    Route::get('/my-submissions', [ExamSubmissionController::class, 'index']);

    Route::apiResource('answers', AnswerController::class)->only(['store', 'update', 'show']);
});