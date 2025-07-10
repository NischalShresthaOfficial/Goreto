<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatNotification extends Model
{
    protected $fillable = [
        'title',
        'content',
        'sender_id',
        'recipient_id',
        'chat_id',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }
}
