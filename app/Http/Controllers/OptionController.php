<?php

namespace App\Http\Controllers;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OptionController extends Controller
{
    public function index()
    {
        $options = Option::with('question')->get();

        $data = $options->map(function ($option) {
            return [
                'id'            => $option->id,
                'option_text'   => $option->option_text,
                'is_correct'    => $option->is_correct,
                'question_text' => $option->question->question_text ?? null,
            ];
        });

        return apiResponse($data, 'Success get options with question text', true, 200);
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
    
            return apiResponse($option, 'option created successfully', true, 201);
        } catch (\Exception $e) {
            Log::error('failed to create option: ' . $e->getMessage());
    
            return apiResponse(null, 'failed to create option', false, 500);
        }
    }

    public function show(Option $option)
    {
        try {
            return apiResponse($option, 'success in obtaining option', true, 200);
        } catch (\Exception $e) {
            Log::error('failed to display options: ' . $e->getMessage());
    
            return apiResponse(null, 'failed to display options.', false, 500);
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
    
            return apiResponse($option, 'option updated successfully', true, 200);
        } catch (\Exception $e) {
            Log::error('failed to update option: ' . $e->getMessage());
    
            return apiResponse(null, 'failed to update option.', false, 500);
        }
    }

    public function destroy(Option $option)
    {
        try {
            $option->delete();
    
            return apiResponse(null, 'option deleted successfully', true, 200);
        } catch (\Exception $e) {
            Log::error('failed to delete option: ' . $e->getMessage());
    
            return apiResponse(null, 'failed to delete option.', false, 500);
        }
    }

    public function listOptions($question_id){
        try {
            $columns = ['id', 'question_id', 'option_text'];

            if (auth()->user()->role === 'admin') {
                $columns[] = 'is_correct';
            }

            $options = Option::select($columns)
                ->where('question_id', $question_id)
                ->get();
                
            return apiResponse($options, 'success in obtaining options', true, 200);
        } catch (\Exception $e) {
            Log::error('failed to retrieve option data: ' . $e->getMessage());
    
            return apiResponse(null, 'failed to retrieve option data.', false, 500);
        }
    }
}
