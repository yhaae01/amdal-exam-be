<?php

if (!function_exists('apiResponse')) {
    function apiResponse($data = null, $message = 'OK', $success = true, $code = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}