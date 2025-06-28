<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Your Password Has Been Reset')
            ->view('emails.password_reset_mail')
            ->with([
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]);
    }
}
