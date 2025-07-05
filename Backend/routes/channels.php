<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return $user->userChats()->where('chat_id', $chatId)->exists();
});
