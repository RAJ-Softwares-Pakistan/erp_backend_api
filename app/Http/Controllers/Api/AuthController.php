<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    /**
     * Authenticate user 
     * @param Request $request The request containing email and password
     * @return JsonResponse The response containing access token, refresh token and user data
     * @throws ValidationException When credentials are invalid
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        
        // Revoke existing tokens if needed
        $user->tokens()->where('name', 'auth_token')->delete();
        $user->tokens()->where('name', 'refresh_token')->delete();
        
        // Create access token
        $accessToken = $user->createToken('auth_token', ['*'])->plainTextToken;
        
        // Create refresh token
        $refreshToken = $user->createToken('refresh_token', ['refresh'])->plainTextToken;

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'   => 'Bearer',
            'user'         => $user,
            'expires_in'   => config('sanctum.expiration', 60 * 24 * 7), // 7 days in minutes
        ]);
    }

    /**
     * Logout user.
     *
     * @param Request $request The request containing the authenticated user
     * @return JsonResponse A success message
     */
    public function logout(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        // Revoke all tokens
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Refresh the authentication tokens.
     *
     * 
     * @param Request $request The request containing the refresh token
     * @return JsonResponse New access and refresh tokens
     */
    public function refreshToken(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        // Get the current refresh token
        $currentToken = $user->currentAccessToken();
        
        // Verify it's a refresh token
        if ($currentToken->name !== 'refresh_token') {
            return response()->json([
                'message' => 'Invalid token type. Please use a refresh token.'
            ], 401);
        }
        
        // Revoke all existing tokens
        $user->tokens()->delete();
        
        // Create new access token
        $accessToken = $user->createToken('auth_token', ['*'])->plainTextToken;
        
        // Create new refresh token
        $refreshToken = $user->createToken('refresh_token', ['refresh'])->plainTextToken;

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'   => 'Bearer',
            'expires_in'   => config('sanctum.expiration', 60 * 24 * 7),
        ]);
    }

    /**
     * Get the authenticated user's profile.
     *
     * @param Request $request The request containing the authenticated user
     * @return JsonResponse The authenticated user's data
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
