<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    public $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message->load('chat');
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.'.$this->message->chat_id);
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'messages' => $this->message->messages,
            'chat_id' => $this->message->chat_id,
            'sent_by' => $this->message->sent_by,
            'sent_at' => $this->message->sent_at,
        ];
    }
}
