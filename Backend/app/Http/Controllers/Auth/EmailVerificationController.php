<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|digits:6',
        ]);

        $user = User::where('email_verification_token', $request->token)->first();

        if (! $user) {
            return response()->json(['message' => 'Invalid or expired token.'], 422);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email is already verified.'], 200);
        }

        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->save();

        return response()->json(['message' => 'Email verified successfully.']);
    }
}
