<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $loginTime;

    public $logoCid;

    public function __construct($loginTime)
    {
        $this->loginTime = $loginTime;
    }

    public function build()
    {
        $logoPath = public_path('assets/logo.png');

        return $this->subject('Login Notification')
            ->view('emails.login_notification')
            ->with('loginTime', $this->loginTime)
            ->with('logoCid', function ($message) use ($logoPath) {
                return $message->embedFromPath($logoPath);
            });
    }
}
