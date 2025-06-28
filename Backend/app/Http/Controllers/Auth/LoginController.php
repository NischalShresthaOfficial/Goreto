<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginNotificationMail;
use App\Models\EmailNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();

        if (! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Your email is not verified. Please verify your email before logging in.',
            ], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $loginTime = Carbon::now()->toDateTimeString();

        Mail::to($user->email)->queue(new LoginNotificationMail($loginTime));

        $systemEmail = config('mail.from.address');
        $systemUser = User::where('email', $systemEmail)->first();

        EmailNotification::create([
            'title' => 'Login Notification',
            'description' => 'Login occurred at '.$loginTime,
            'sender_id' => $systemUser?->id,
            'receiver_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role_name,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
