<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallAnswered implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $senderId,
        public int $receiverId,
        public string $sdp,
        public ?int $chatId = null,
    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel('call.'.$this->receiverId);
    }

    public function broadcastWith()
    {
        return [
            'sender_id' => $this->senderId,
            'sdp' => $this->sdp,
            'chat_id' => $this->chatId,
        ];
    }
}
