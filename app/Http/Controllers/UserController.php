<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $search = $request->query('search');

            $query = User::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('role', 'like', '%' . $search . '%');
                });
            }

            $users = $query->orderBy('name')->paginate(10);

            return apiResponse($users, 'success in obtaining users', true, 200);
        } catch (\Exception $e) {
            Log::error('Failed to get users: ' . $e->getMessage());
            return apiResponse(null, 'failed to retrieve users', false, 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'      => 'required|string|max:255',
                'email'     => 'required|string|email|unique:users,email',
                'password'  => 'required|string|min:6',
                'role'      => 'in:user,admin',
                'is_active' => 'boolean',
            ]);

            $user = User::create([
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'role'      => $request->role ?? 'user',
                'is_active' => $request->is_active ?? true,
            ]);

            return apiResponse($user, 'user created successfully', true, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create user: ' . $e->getMessage());
            return apiResponse(null, 'failed to create user', false, 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $request->validate([
                'name'      => 'sometimes|required|string|max:255',
                'email'     => 'sometimes|required|string|email|unique:users,email,' . $id,
                'password'  => 'nullable|string|min:6',
                'role'      => 'in:user,admin',
                'is_active' => 'boolean',
            ]);

            $user->name = $request->name ?? $user->name;
            $user->email = $request->email ?? $user->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            if ($request->has('role')) {
                $user->role = $request->role;
            }
            if ($request->has('is_active')) {
                $user->is_active = $request->is_active;
            }

            $user->save();

            return apiResponse($user, 'user updated successfully', true, 200);
        } catch (\Exception $e) {
            Log::error('Failed to update user: ' . $e->getMessage());
            return apiResponse(null, 'failed to update user', false, 500);
        }
    }

    public function show($id) {
        try {
            $exam = User::findOrFail($id);
            return apiResponse($exam, 'success in obtaining user', true, 200);
        } catch (\Exception $e) {
            Log::error('failed to retrieve user data: ' . $e->getMessage());
    
            return apiResponse(null, 'failed to retrieve user data.', false, 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return apiResponse(null, 'user deleted successfully', true, 200);
        } catch (\Exception $e) {
            Log::error('Failed to delete user: ' . $e->getMessage());
            return apiResponse(null, 'failed to delete user', false, 500);
        }
    }

    public function user_not_submitted_yet()
    {
        try {

            $users = DB::table('exam_batch_users as ebu')
            ->join('users', 'users.id', '=', 'ebu.user_id')
            ->leftJoin('exam_submissions', 'exam_submissions.user_id', '=', 'ebu.user_id')
            ->whereNull('exam_submissions.submitted_at')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'exam_submissions.submitted_at'
            )
            ->distinct()
            ->paginate(20);

            return apiResponse($users, 'user get successfully', true, 200);
        } catch (\Exception $e) {
            Log::error('Failed to get user: ' . $e->getMessage());
            return apiResponse(null, 'failed to get user', false, 500);
        }
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        if ($validator->fails()) {
            return apiResponse(null, 'Invalid file upload', false, 422);
        }

        try {
            Excel::queueImport(new UsersImport, $request->file('file'));
            return apiResponse(null, 'Import is being processed in background', true, 200);
        } catch (\Exception $e) {
            Log::error('User import error: ' . $e->getMessage());
            return apiResponse(null, 'Import failed', false, 500);
        }
    }

    public function user_not_assign_batch()
    {
        try {
            $users = User::whereDoesntHave('examBatchUsers')->where('role', 'user')->get();
            return apiResponse($users, 'user get successfully', true, 200);
        } catch (\Exception $e) {
            Log::error('Failed to get user: ' . $e->getMessage());
            return apiResponse(null, 'failed to get user', false, 500);
        }
    }

    public function result_qualified(Request $request)
    {
        try {
            $keyword = $request->query('u');

            if (!$keyword) {
                return response()->json([
                    'message' => 'Keyword is required',
                ], 400);
            }

            $results = DB::select("
                SELECT
                    users.name,
                    users.nik,
                    exams.title,
                    exam_submissions.started_at,
                    users.is_qualified,
                    exam_submissions.submitted_at,
                    SUM(
                        CASE
                            WHEN selected_option.is_correct = true THEN questions.weight
                            ELSE 0
                        END
                    ) AS exam_submission_score,
                    COALESCE(
                        exam_submissions.score,
                        SUM(
                            CASE
                                WHEN selected_option.is_correct = true THEN questions.weight
                                ELSE 0
                            END
                        ) * 5
                    ) AS total_score_fix,
                    CASE
                        WHEN exam_submissions.submitted_at IS NULL THEN '15 Menit'
                        ELSE CONCAT(
                            ROUND(EXTRACT(EPOCH FROM (exam_submissions.submitted_at - exam_submissions.started_at)) / 60.0),
                            ' Menit'
                        )
                    END AS duration,
                    RANK() OVER (
                        PARTITION BY exams.title
                        ORDER BY
                            COALESCE(
                                exam_submissions.score,
                                SUM(
                                    CASE
                                        WHEN selected_option.is_correct = true THEN questions.weight
                                        ELSE 0
                                    END
                                ) * 5
                            ) DESC,
                            EXTRACT(EPOCH FROM (exam_submissions.submitted_at - exam_submissions.started_at)) ASC
                    ) AS ranking
                FROM users
                JOIN exam_submissions ON users.id = exam_submissions.user_id
                JOIN exams ON exams.id = exam_submissions.exam_id
                JOIN answers ON exam_submissions.id = answers.exam_submission_id
                JOIN questions ON answers.question_id = questions.id
                LEFT JOIN options AS selected_option ON answers.selected_option_id = selected_option.id
                WHERE users.is_qualified = true
                AND users.nik ILIKE ?
                GROUP BY
                    users.id,
                    exams.title,
                    users.nik,
                    users.name,
                    exam_submissions.id,
                    exam_submissions.submitted_at,
                    exam_submissions.started_at,
                    users.is_qualified,
                    exam_submissions.score
                ORDER BY
                    total_score_fix DESC,
                    EXTRACT(EPOCH FROM (exam_submissions.submitted_at - exam_submissions.started_at)) ASC
            ", [
                "%$keyword%"
            ]);

            return apiResponse($results, 'user get successfully', true, 200);
        } catch (\Exception $e) {
            Log::error('Failed to get user: ' . $e->getMessage());
            return apiResponse(null, $e->getMessage(), false, 500);
        }
    }
    public function getQualified(Request $request)
    {
        try {
            // Cek apakah nik ada di query string atau di body request
            $nik = $request->input('nik') ?? $request->query('nik');
            if ($nik) {
                // Cari user berdasarkan nik dan pastikan dia qualified
                $qualifiedUser = User::where('nik', $nik)
                    ->where('is_qualified', true)
                    ->with('submissions')
                    ->with(['submissions', 'exams'])
                    ->first();

                if ($qualifiedUser) {
                    // Cek apakah user memiliki relasi dengan submissions
                    $submissions = $qualifiedUser->submissions;

                    if ($submissions->isEmpty()) {
                        // Jika user tidak memiliki relasi dengan exam submissions
                        return apiResponse(null, 'doesnt-have-exam-submissions', false, 404);
                    }

                    // Menambahkan judul dari exam jika ada
                    $examTitle = $qualifiedUser->exams ? $qualifiedUser->exams->title : null;

                    // Jika user ditemukan, qualified, memiliki exam submissions, dan title exam
                    return apiResponse([
                        'user'       => $qualifiedUser,
                        'exam_title' => $examTitle,
                    ], 'user-found', true, 200);
                } else {
                    // Jika nik ditemukan tapi user tidak qualified atau tidak ditemukan
                    return apiResponse(null, 'not-registered', false, 404);
                }
            } else {
                // Jika nik tidak diberikan atau kosong, berikan pesan atau hindari pencarian data
                return apiResponse(null, 'nik-not-provided', false, 400);
            }
        } catch (\Exception $e) {
            Log::error('Failed to get qualified users: ' . $e->getMessage());
            return apiResponse(null, 'failed to retrieve qualified users', false, 500);
        }
    }
}
