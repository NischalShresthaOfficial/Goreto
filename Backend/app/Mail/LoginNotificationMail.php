<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginNotificationMail extends Mailable implements ShouldQueue
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
            ->with(['loginTime' => $this->loginTime])
            ->withSwiftMessage(function ($message) use ($logoPath) {
                $this->logoCid = $message->embedFromPath($logoPath);
            });
    }
}
