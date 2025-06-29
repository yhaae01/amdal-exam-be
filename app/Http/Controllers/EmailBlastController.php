<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UserEmailBlastImport;
use Illuminate\Support\Facades\Validator;

class EmailBlastController extends Controller
{
    public function blastEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid file upload']);
        }

        try {
            Excel::queueImport(new UserEmailBlastImport, $request->file('file'));
            return response()->json(['success' => true, 'message' => 'Emails are being sent in background']);
        } catch (\Exception $e) {
            Log::error('Email blast failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to start email blast']);
        }
    }
}
