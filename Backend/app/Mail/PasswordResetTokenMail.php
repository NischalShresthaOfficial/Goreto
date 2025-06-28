<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetTokenMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $token;

    public $user;

    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function build()
    {
        return $this->subject('Password Reset Request')
            ->view('emails.password_reset')
            ->with([
                'name' => $this->user->name,
                'token' => $this->token,
                'email' => $this->user->email,
            ]);
    }
}
