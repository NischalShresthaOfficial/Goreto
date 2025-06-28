<?php

// namespace App\Http\Controllers\Auth;

// use App\Http\Controllers\Controller;
// use App\Mail\PasswordResetMail;
// use App\Models\EmailNotification;
// use App\Models\User;
// use Illuminate\Auth\Events\PasswordReset;
// use Illuminate\Http\JsonResponse;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\Password;
// use Illuminate\Support\Str;
// use Illuminate\Validation\Rules;
// use Illuminate\Validation\ValidationException;

// class NewPasswordController extends Controller
// {
//     /**
//      * Handle an incoming new password request.
//      *
//      * @throws \Illuminate\Validation\ValidationException
//      */
//     public function store(Request $request): JsonResponse
//     {
//         $request->validate([
//             'token' => ['required'],
//             'email' => ['required', 'email'],
//             'password' => ['required', 'confirmed', Rules\Password::defaults()],
//         ]);

//         $status = Password::reset(
//             $request->only('email', 'password', 'password_confirmation', 'token'),
//             function ($user) use ($request) {
//                 $user->forceFill([
//                     'password' => Hash::make($request->string('password')),
//                     'remember_token' => Str::random(60),
//                 ])->save();

//                 Mail::to($user->email)->queue(new PasswordResetMail($user));

//                 $systemEmail = config('mail.from.address');
//                 $systemUser = User::where('email', $systemEmail)->first();

//                 EmailNotification::create([
//                     'title' => 'Password Reset Successful',
//                     'description' => 'Your password was successfully reset.',
//                     'sender_id' => $systemUser?->id,
//                     'receiver_id' => $user->id,
//                 ]);

//                 event(new PasswordReset($user));
//             }
//         );

//         if ($status != Password::PASSWORD_RESET) {
//             throw ValidationException::withMessages([
//                 'email' => [__($status)],
//             ]);
//         }

//         return response()->json(['status' => __($status)]);
//     }
// }
