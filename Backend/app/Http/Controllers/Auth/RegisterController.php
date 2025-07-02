<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegisterNotificationMail;
use App\Models\Country;
use App\Models\EmailNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'country' => 'nullable|string|exists:countries,country',
        ]);

        $country = null;
        if (! empty($validated['country'])) {
            $country = Country::where('country', $validated['country'])->first();
            if (! $country) {
                return response()->json(['message' => 'Country not found'], 422);
            }
        }

        $role = Role::where('name', 'user')->first();

        $token = random_int(100000, 999999);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'country_id' => $country?->id,
            'role_id' => $role->id,
            'email_verification_token' => $token,
        ]);

        Mail::to($user->email)->queue(new RegisterNotificationMail($user, $token));

        $user->assignRole('user');

        $systemUser = User::where('email', config('mail.from.address'))->first();

        EmailNotification::create([
            'title' => 'Registration Email Sent',
            'description' => 'A verification email was sent to '.$user->email,
            'sender_id' => $systemUser?->id,
            'receiver_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => 'user',
                'country' => $country?->country,
            ],
        ], 201);
    }
}
