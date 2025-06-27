<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Forbidden.'
            ], 403);
        }

        $questions = Question::with('options')->orderBy('order')->paginate(10);

        return response()->json([
            'data' => $questions
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'exam_id'       => 'required|exists:exams,id',
                'question_text' => 'required|string',
                'image'         => 'nullable|file|image|max:2048',
                'order'         => 'nullable|integer',
                'question_type' => 'in:multiple_choice,essay',
                'weight'        => 'nullable|numeric'
            ]);
    
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('questions', 'public');
                $validated['image'] = $path;
            }
    
            $question = Question::create($validated);
    
            return response()->json([
                'message' => 'Soal berhasil ditambahkan.',
                'data' => $question
            ], 201);
    
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan soal: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menyimpan soal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show(Question $question)
    {
        try {
            $question->load(['options', 'answers']);
            return response()->json([
                'data' => $question
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menampilkan question: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'Gagal mengambil data soal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Question $question)
    {
        try {
            $validated = $request->validate([
                'question_text' => 'sometimes|required|string',
                'image'         => 'nullable|file|image|max:2048',
                'order'         => 'nullable|integer',
                'question_type' => 'in:multiple_choice,essay',
                'weight'        => 'nullable|numeric'
            ]);
    
            if ($request->hasFile('image')) {
                if ($question->image && Storage::disk('public')->exists($question->image)) {
                    Storage::disk('public')->delete($question->image);
                }
    
                $validated['image'] = $request->file('image')->store('questions', 'public');
            }
    
            $question->update($validated);
    
            return response()->json([
                'message' => 'Soal berhasil diperbarui.',
                'data' => $question
            ]);
    
        } catch (\Exception $e) {
            Log::error('Gagal mengupdate soal: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal mengupdate soal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Question $question)
    {
        try {
            if ($question->image && Storage::disk('public')->exists($question->image)) {
                Storage::disk('public')->delete($question->image);
            }
    
            $question->delete();
    
            return response()->json([
                'message' => 'Soal berhasil dihapus.'
            ]);
    
        } catch (\Exception $e) {
            Log::error('Gagal menghapus soal: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menghapus soal.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
