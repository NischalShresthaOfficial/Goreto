<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetTokenMail;
use App\Models\EmailNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class PasswordResetLinkController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $token = Password::createToken($user);

        Mail::to($user->email)->queue(new PasswordResetTokenMail($user, $token));

        $systemEmail = config('mail.from.address');

        $systemUser = User::where('email', $systemEmail)->first();

        EmailNotification::create([
            'title' => 'Password Reset Token Sent',
            'description' => 'A password reset token was generated and emailed to '.$user->email,
            'sender_id' => $systemUser?->id,
            'receiver_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Reset token generated successfully.',
        ]);
    }
}
