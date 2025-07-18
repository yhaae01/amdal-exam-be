<?php

namespace App\Http\Controllers;

use App\Models\ExamBatchUser;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return apiResponse(null, 'login failed, email or password does not match', false, 401);
        }

        $user = auth()->user();

        $batchUser = ExamBatchUser::with('examBatch')->where('user_id', $user->id)->first();

        // Default values
        $user->exam_id = null;
        $user->start_exam = null;
        $user->submited_at = null;
        $user->batch = null;
        $user->batch_start_time = null;
        $user->batch_end_time = null;

        if ($batchUser && $batchUser->examBatch) {
            $submission = $user->submissions()
                ->select('started_at', 'submitted_at')
                ->where('exam_id', $batchUser->exam_id)
                ->where('exam_batch_id', $batchUser->examBatch->id)
                ->where('user_id', $user->id)
                ->first();

            $user->exam_id = $batchUser->exam_id;
            $user->start_exam = $submission->started_at ?? null;
            $user->submited_at = $submission->submitted_at ?? null;
            $user->batch = $batchUser->examBatch->name;
            $user->batch_start_time = $batchUser->examBatch->start_time;
            $user->batch_end_time = $batchUser->examBatch->end_time;
        }

        $data = [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'user'         => $user
        ];

        return apiResponse($data, 'login successful', true, 200);
    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return apiResponse(null, 'user logged out successfully', true, 200);
        } catch (JWTException $e) {
            return apiResponse(null, 'failed to logout, token invalid', false, 500);
        }
    }

    public function me()
    {
        $user = auth()->user();

        $batchUser = ExamBatchUser::with('examBatch')
            ->where('user_id', $user->id)
            ->first();

        // Set nilai default
        $user->start_exam = null;
        $user->submited_at = null;
        $user->exam_id = null;
        $user->batch = null;
        $user->batch_start_time = null;
        $user->batch_end_time = null;

        if ($batchUser && $batchUser->examBatch) {
            $submission = $user->submissions()
                ->select('started_at', 'submitted_at')
                ->where('exam_id', $batchUser->exam_id)
                ->where('exam_batch_id', $batchUser->examBatch->id)
                ->where('user_id', $user->id)
                ->first();

            $user->start_exam = $submission->started_at ?? null;
            $user->submited_at = $submission->submitted_at ?? null;
            $user->exam_id = $batchUser->exam_id;
            $user->batch = $batchUser->examBatch->name;
            $user->batch_start_time = $batchUser->examBatch->start_time;
            $user->batch_end_time = $batchUser->examBatch->end_time;
        }

        return apiResponse($user, 'success in obtaining personal information', true, 200);
    }
}
