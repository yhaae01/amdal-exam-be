<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

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
}
