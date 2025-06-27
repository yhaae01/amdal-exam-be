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
                
        $user->batch = $batchUser->examBatch->name       ?? null;
        $user->start_time = $batchUser->examBatch->start_time ?? null;
        $user->end_time = $batchUser->examBatch->end_time   ?? null;

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
        $batchUser = ExamBatchUser::with('examBatch')->where('user_id', auth()->user()->id)->first();
               
        $user = auth()->user();

        $user->batch = $batchUser->examBatch->name       ?? null;
        $user->start_time = $batchUser->examBatch->start_time ?? null;
        $user->end_time = $batchUser->examBatch->end_time   ?? null;

        return apiResponse($user, 'success in obtaining personal information', true, 200);
    }
}
