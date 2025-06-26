<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::withCount('questions')->get();

        return response()->json([
            'data' => $exams
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'         => 'required|string',
                'description'   => 'nullable|string',
                'image'         => 'nullable|file|image|max:2048',
                'duration'      => 'required|integer',
            ]);
    
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('exams', 'public');
                $validated['image'] = $path;
            }
    
            $exam = Exam::create($validated);
    
            return response()->json([
                'message' => 'Data berhasil ditambahkan.',
                'data' => $exam
            ], 201);
    
        } catch (\Exception $e) {
            Log::error('Ada error di : ' . $e->getMessage());
    
            return response()->json([
                'message' => 'Gagal menyimpan ujian.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $exam = Exam::with([
                'questions.options',
                'questions.answers' => function ($q) {
                    $q->whereHas('exam_submission', function ($q2) {
                        $q2->where('user_id', auth()->id());
                    });
                }
            ])->findOrFail($id);
    
            return response()->json([
                'data' => $exam
            ]);
    
        } catch (\Exception $e) {
            Log::error('Gagal mengambil ujian: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'Gagal mengambil data ujian.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Exam $exam)
    {
        try {
            $validated = $request->validate([
                'title'         => 'sometimes|required|string',
                'description'   => 'nullable|string',
                'image'         => 'nullable|file|image|max:2048',
                'duration'      => 'integer',
            ]);
    
            if ($request->hasFile('image')) {
                if ($exam->image && Storage::disk('public')->exists($exam->image)) {
                    Storage::disk('public')->delete($exam->image);
                }
    
                $path = $request->file('image')->store('exams', 'public');
                $validated['image'] = $path;
            }
    
            $exam->update($validated);
    
            return response()->json([
                'message' => 'Data berhasil diperbarui.',
                'data' => $exam
            ]);
    
        } catch (\Exception $e) {
            Log::error('Gagal update ujian: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'Gagal update ujian.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Exam $exam)
    {
        try {
            if ($exam->submissions()->exists()) {
                return response()->json([
                    'message' => 'Tidak dapat menghapus ujian yang sudah dikerjakan.'
                ], 400);
            }

            if ($exam->image && Storage::disk('public')->exists($exam->image)) {
                Storage::disk('public')->delete($exam->image);
            }

            $exam->delete();

            return response()->json([
                'message' => 'Ujian berhasil dihapus.'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Gagal menghapus ujian: ' . $e->getMessage());

            return response()->json([
                'message' => 'Gagal menghapus ujian.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
