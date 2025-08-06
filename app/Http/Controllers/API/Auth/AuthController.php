<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Throttle Key
        $throttleKey = Str::lower($request->email) . '|' . $request->ip();

        // Too many attempts?
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'message' => 'Too many login attempts. Please try again in ' . RateLimiter::availableIn($throttleKey) . ' seconds.'
            ], 429);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ], [
            'email.required' => 'This field is required',
            'email.email' => 'Invalid email',
            'password.required' => 'This field is required',
            'password.string' => 'Invalid inputs',
            'password.min' => 'Password is too short'
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 60); // Add delay for brute force protection
            return response()->json([
                'message' => 'Sign in was not successful. Try again later.'
            ], 401);
        }

        // Clear any previous throttle attempts
        RateLimiter::clear($throttleKey);

        // Delete old tokens
        $user->tokens()->delete();

        // Issue token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful, redirecting...',
            'token' => $token,
            'user' => $user,
            'role' => $user->role // Explicitly include role
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
