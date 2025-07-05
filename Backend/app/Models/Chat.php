<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'name',
        'is_group',
        'created_by',
        'image_path',
        'created_at',
    ];

    public function userChats()
    {
        return $this->hasMany(UserChat::class);
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_user', 'chat_id', 'user_id')
            ->using(UserChat::class)
            ->withTimestamps();
    }
}
