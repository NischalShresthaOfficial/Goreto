<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetTokenMail extends Mailable
{
    use SerializesModels;

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
