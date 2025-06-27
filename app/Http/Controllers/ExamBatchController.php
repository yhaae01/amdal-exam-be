<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ExamBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExamBatchController extends Controller
{
    public function index()
    {
        return ExamBatch::with('exam')->paginate(10);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'exam_id'          => 'required|exists:exams,id',
                'name'             => 'required|string',
                'start_time'       => 'required|date',
                'end_time'         => 'required|date|after:start_time',
                'max_participants' => 'nullable|integer'
            ]);

            $batch = ExamBatch::create($validated);

            return response()->json([
                'message' => 'Batch berhasil dibuat',
                'data' => $batch
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error saat membuat batch: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal membuat batch', 'error' => $e->getMessage()], 500);
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

            $batch->users()->syncWithoutDetaching($users);

            return response()->json([
                'message' => 'Users berhasil di-assign ke batch'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error saat assign user ke batch: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal assign user', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $batch = ExamBatch::with(['exam', 'users'])->findOrFail($id);
            return response()->json($batch);
        } catch (\Exception $e) {
            Log::error('Error saat mengambil batch: ' . $e->getMessage());
            return response()->json(['message' => 'Batch tidak ditemukan', 'error' => $e->getMessage()], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $batch = ExamBatch::findOrFail($id);
            $batch->delete();

            return response()->json([
                'message' => 'Batch berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error saat menghapus batch: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus batch', 'error' => $e->getMessage()], 500);
        }
    }
}
