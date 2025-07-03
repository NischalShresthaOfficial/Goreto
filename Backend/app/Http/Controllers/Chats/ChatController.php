<?php

namespace App\Http\Controllers\Chats;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function createOrGetOneOnOne(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|different:'.Auth::id(),
        ]);

        $currentUserId = Auth::id();
        $otherUserId = $request->user_id;

        $chat = Chat::where('is_group', false)
            ->whereHas('users', function ($q) use ($currentUserId) {
                $q->where('user_id', $currentUserId);
            })
            ->whereHas('users', function ($q) use ($otherUserId) {
                $q->where('user_id', $otherUserId);
            })
            ->first();

        if (! $chat) {
            $chat = Chat::create([
                'is_group' => false,
                'created_by' => $currentUserId,
            ]);

            $chat->users()->attach([$currentUserId, $otherUserId]);
        }

        return response()->json([
            'message' => 'Chat fetched or created successfully',
            'chat' => $chat->load('users'),
        ]);
    }
}
