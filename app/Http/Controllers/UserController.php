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
}
