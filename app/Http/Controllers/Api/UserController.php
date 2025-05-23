<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Register a new user
     */
    public function register(RegisterUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => User::ROLES['user'], // Default role for new users
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Get authenticated user profile
     */
    public function profile(): JsonResponse
    {
        return response()->json([
            'user' => Auth::user()
        ]);
    }

    /**
     * Update user profile
     */
    public function update(UpdateUserRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update($request->validated());

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }
        
        /** @var \App\Models\User $user */
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * List all users (Admin and Owner only)
     */
    public function index(): JsonResponse
    {
        if (!Gate::allows('viewAny', User::class)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
        
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Delete a user (Admin and Owner only)
     */
    public function destroy(User $user): JsonResponse
    {
        if (!Gate::allows('delete', $user)) {
            throw new AuthorizationException('This action is unauthorized.');
        }
        
        $user->delete();
        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Change user role (Admin only)
     */
    public function changeRole(Request $request, User $user): JsonResponse
    {
        if (!Gate::allows('changeRole', $user)) {
            throw new AuthorizationException('This action is unauthorized.');
        }

        $request->validate([
            'role' => ['required', 'string', 'in:' . implode(',', User::ROLES)]
        ]);

        $user->update([
            'role' => $request->role
        ]);

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $user
        ]);
    }
} 