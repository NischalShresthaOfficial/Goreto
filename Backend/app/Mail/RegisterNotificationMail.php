<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegisterNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;

    public $token;

    public $logoCid;

    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function build()
    {
        $logoPath = public_path('assets/logo.png');

        return $this->subject('Welcome to '.config('app.name').' – Verify Your Email')
            ->view('emails.register_notification')
            ->with([
                'user' => $this->user,
                'token' => $this->token,
            ])
            ->withSwiftMessage(function ($message) use ($logoPath) {
                $this->logoCid = $message->embedFromPath($logoPath);
            });
    }
}
