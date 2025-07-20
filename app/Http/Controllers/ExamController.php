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
        $exams = Exam::withCount('questions')->paginate(10);

        return apiResponse($exams, 'success in obtaining exams', true, 200);
    }

    public function getAllExams(Request $request)
    {
        try {
             // tidak difilter user
            $isAdmin = auth()->guard()->user()->role === 'admin';

            // $with = [
            //     // 'questions.options',
            //     // 'questions.answers.examSubmission'
            // ];

            $query = Exam::withCount('questions');
            
            $search = $request->query('search');
            if ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            }

            $exams = $isAdmin ? $query->paginate(10) : $query->get();

            // jika ingin mengambil data ujian yang sudah dikerjakan oleh user
            // $exams = Exam::with([
            //     'questions.options',
            //     'questions.answers' => function ($q) {
            //         $q->whereHas('examSubmission', function ($q2) {
            //             $q2->where('user_id', auth()->id());
            //         });
            //     }
            // ])->get();

            return apiResponse($exams, 'success in obtaining exams', true, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve complete exam data: ' . $e->getMessage());

            return apiResponse(null, 'failed to retrieve complete exam data.', false, 500);
        }
    }

    public function getAllExamsWithoutPaginate(Request $request)
    {
        try {
            $query = Exam::withCount('questions');
            
            $search = $request->query('search');
            if ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            }

            if ($request->has('year') && is_numeric($request->year)) {
                $query->whereYear('created_at', $request->year);
            }

            // $exams = $isAdmin ? $query->paginate(10) : $query->get();
            $exams = $query->get();

            // jika ingin mengambil data ujian yang sudah dikerjakan oleh user
            // $exams = Exam::with([
            //     'questions.options',
            //     'questions.answers' => function ($q) {
            //         $q->whereHas('examSubmission', function ($q2) {
            //             $q2->where('user_id', auth()->id());
            //         });
            //     }
            // ])->get();

            return apiResponse($exams, 'success in obtaining exams', true, 200);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve complete exam data: ' . $e->getMessage());

            return apiResponse(null, 'failed to retrieve complete exam data.', false, 500);
        }
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
    
            return apiResponse($exam, 'data added successfully.', true, 201);
        } catch (\Exception $e) {
            Log::error('failed to save test: ' . $e->getMessage());
    
            return apiResponse(null, 'failed to save test.', false, 500);
        }
    }

    public function show($id)
    {
        try {
            $exam = Exam::with([
                'questions.options',
                'questions.answers' => function ($q) {
                    $q->whereHas('examSubmission', function ($q2) {
                        $q2->where('user_id', auth()->guard()->id());
                    });
                }
            ])->findOrFail($id);
    
            return apiResponse($exam, 'success in obtaining exam', true, 200);
        } catch (\Exception $e) {
            Log::error('failed to retrieve exam data: ' . $e->getMessage());
    
            return apiResponse(null, 'failed to retrieve exam data.', false, 500);
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
    
            return apiResponse($exam, 'data updated successfully.', true, 200);
        } catch (\Exception $e) {
            Log::error('failed to update exam: ' . $e->getMessage());
    
            return apiResponse(null, 'failed to update exam.', false, 500);
        }
    }

    public function destroy(Exam $exam)
    {
        try {
            if ($exam->submissions()->exists()) {
                return apiResponse(null, 'cant delete completed exams.', false, 400);
            }

            if ($exam->image && Storage::disk('public')->exists($exam->image)) {
                Storage::disk('public')->delete($exam->image);
            }

            $exam->delete();

            return apiResponse(null, 'exam deleted successfully.', true, 200);
        } catch (\Exception $e) {
            Log::error('failed to delete exam: ' . $e->getMessage());

            return apiResponse(null, 'failed to delete exam.', false, 500);
        }
    }
}
