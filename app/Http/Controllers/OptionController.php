<?php

namespace App\Http\Controllers;

use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OptionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id'
        ]);
    
        $options = Option::where('question_id', $request->question_id)->get();
    
        return response()->json([
            'data' => $options
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'question_id' => 'required|exists:questions,id',
                'option_text' => 'required|string',
                'is_correct'  => 'boolean',
            ]);
    
            $option = Option::create($validated);
    
            return response()->json([
                'message' => 'Opsi berhasil ditambahkan.',
                'data' => $option
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Gagal membuat option: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'Gagal menyimpan opsi jawaban.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Option $option)
    {
        try {
            return response()->json([
                'message' => 'Opsi berhasil diperbarui.',
                'data' => $option
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal menampilkan option: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'Gagal mengambil data opsi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Option $option)
    {
        try {
            $validated = $request->validate([
                'option_text' => 'sometimes|required|string',
                'is_correct'  => 'boolean',
            ]);
    
            $option->update($validated);
    
            return response()->json($option);
    
        } catch (\Exception $e) {
            Log::error('Gagal update option: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'Gagal mengupdate opsi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Option $option)
    {
        try {
            $option->delete();
    
            return response()->json(['message' => 'Option deleted']);
    
        } catch (\Exception $e) {
            Log::error('Gagal menghapus option: ' . $e->getMessage());
    
            return response()->json([
                'message' => 'Gagal menghapus opsi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
