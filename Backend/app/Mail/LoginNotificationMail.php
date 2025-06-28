<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginNotificationMail extends Mailable
{
    use SerializesModels;

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
            ->with([
                'loginTime' => $this->loginTime,
            ])
            ->withSwiftMessage(function ($message) use ($logoPath) {
                $cid = $message->embedFromPath($logoPath);
                $this->logoCid = $cid;
            });
    }
}
