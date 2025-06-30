<?php

namespace App\Http\Controllers;

use App\Models\ActivityUser;
use App\Models\ExamSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivityController extends Controller
{
    public function addActivity(Request $request)
    {
        $user = auth()->user();
        $examSubmission = ExamSubmission::firstWhere('user_id', $user->id);

        if (!$examSubmission) {
            return apiResponse(null, 'Exam submission not found', false, 404);
        }

        try {
            $activity = ActivityUser::create([
                'exam_submission_id' => $examSubmission->id,
                'user_id' => $user->id
            ]);

            return apiResponse($activity, 'Activity added', true);
        } catch (\Throwable $e) {
            Log::error('Failed to add activity: ' . $e->getMessage());

            return apiResponse(null, 'Failed to add activity', false, 500);
        }
    }

    public function checkCountActivity(Request $request)
    {
        $user = auth()->user();
        $examSubmission = ExamSubmission::firstWhere('user_id', $user->id);

        if (!$examSubmission) {
            return apiResponse(null, 'Exam submission not found', false, 404);
        }

        $count = ActivityUser::where('exam_submission_id', $examSubmission->id)->count();

        return apiResponse($count, 'Count activity', true);
    }
}
