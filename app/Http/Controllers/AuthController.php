<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'user' => Auth::guard('api')->user(),
            'token' => $token,
            'token_type' => 'bearer',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('api')->logout();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    public function refresh()
    {
        $token = Auth::guard('api')->refresh();

        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
        ]);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('email', $googleUser->email)->first();

            if (!$user) {
                $user = User::create([
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                ]);
            } else {
                $user->update(['google_id' => $googleUser->id]);
            }

            $token = JWTAuth::fromUser($user);

            return redirect(config('app.frontend_url', 'http://localhost:3000') . '/dashboard?token=' . $token);
        } catch (\Exception $e) {
            return redirect(config('app.frontend_url', 'http://localhost:3000') . '/login?error=google_auth_failed');
        }
    }
}

