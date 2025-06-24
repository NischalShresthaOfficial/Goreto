<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserChat extends Pivot
{
    protected $fillable = [
        'chat_id',
        'user_id',
    ];

    public $timestamps = true;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }
}
