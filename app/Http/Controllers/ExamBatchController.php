<?php

namespace App\Http\Controllers;


use App\Models\User;
use App\Models\ExamBatch;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ExamBatchController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'admin') {
            return apiResponse(null, 'Forbidden', false, 403);
        }

        $examBatches = ExamBatch::with('exam')->paginate(10);

        return apiResponse($examBatches, 'success in obtaining batches', true, 200);
    }

    public function all()
    {
        if (auth()->user()->role !== 'admin') {
            return apiResponse(null, 'Forbidden', false, 403);
        }

        $examBatches = ExamBatch::all();

        return apiResponse($examBatches, 'success in obtaining batches', true, 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                // 'exam_id'          => 'required|exists:exams,id',
                'name'             => 'required|string',
                'start_time'       => 'required|date',
                'end_time'         => 'required|date',
                'max_participants' => 'nullable|integer'
            ]);

            $batch = ExamBatch::create($validated);

            return apiResponse($batch, 'batch created successfully', true, 201);
        } catch (\Exception $e) {
            Log::error('Error while creating batch: ' . $e->getMessage());
            return apiResponse(null, 'failed to create batch', false, 500);
        }
    }

    public function assignUsers(Request $request, $id)
    {
        try {
            $batch = ExamBatch::findOrFail($id);

            
            $validated = $request->validate([
                'user_ids'   => 'required|array',
                'user_ids.*' => 'exists:users,id'
            ]);

            $users = User::whereIn('id', $validated['user_ids'])
            ->where('role', 'user')
            ->pluck('id');
            
            foreach ($users as $userId) {
                DB::table('exam_batch_users')->updateOrInsert(
                    [
                        'exam_batch_id' => $id,
                        'user_id'       => $userId,
                    ],
                    [
                        'id'         => (string) Str::uuid(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            return apiResponse(null, 'Users successfully assigned to batch', true, 200);
        } catch (\Exception $e) {
            Log::error('Error while assigning user to batch: ' . $e->getMessage());
            return apiResponse(null, "Gagal", false, 500);
        }
    }

    public function show($id)
    {
        try {
            $batch = ExamBatch::with(['exam','users'])->findOrFail($id);
            return apiResponse($batch, 'success in obtaining batch', true, 200);
        } catch (\Exception $e) {
            Log::error('Error while fetching batch: ' . $e->getMessage());
            return apiResponse(null, 'batch not found', false, 404);
        }
    }

    public function destroy($id)
    {
        try {
            $batch = ExamBatch::findOrFail($id);
            $batch->delete();

            return apiResponse(null, 'batch deleted successfully', true, 200);
        } catch (\Exception $e) {
            Log::error('Error while deleting batch: ' . $e->getMessage());
            
            return apiResponse(null, 'failed to delete batch', false, 500);
        }
    }
}
