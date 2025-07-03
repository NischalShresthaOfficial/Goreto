<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Pusher\PushNotifications\PushNotifications;

class ChatPushNotification extends Notification
{
    use Queueable;

    protected $title;

    protected $body;

    protected $userId;

    public function __construct($title, $body, $userId)
    {
        $this->title = $title;
        $this->body = $body;
        $this->userId = $userId;
    }

    public function via($notifiable)
    {
        return ['broadcast'];
    }

    public function toBroadcast($notifiable)
    {
        $this->sendBeamsNotification();

        return new BroadcastMessage([
            'title' => $this->title,
            'body' => $this->body,
        ]);
    }

    protected function sendBeamsNotification()
    {
        $beamsClient = new PushNotifications([
            'instanceId' => env('PUSHER_BEAMS_INSTANCE_ID'),
            'secretKey' => env('PUSHER_BEAMS_SECRET_KEY'),
        ]);

        $beamsClient->publishToUsers(
            [(string) $this->userId],
            [
                'web' => [
                    'notification' => [
                        'title' => $this->title,
                        'body' => $this->body,
                        'deep_link' => env('APP_URL').'/chat',

                    ],
                ],
            ]
        );
    }
}
