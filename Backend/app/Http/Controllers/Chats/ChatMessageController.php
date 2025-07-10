<?php

namespace App\Http\Controllers\Chats;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatNotification;
use App\Models\UserChat;
use App\Notifications\ChatPushNotification;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'messages' => 'required|string',
        ]);

        $senderId = auth()->id();

        $isParticipant = \App\Models\UserChat::where('chat_id', $request->chat_id)
            ->where('user_id', $senderId)
            ->exists();

        if (! $isParticipant) {
            return response()->json([
                'message' => 'You are not authorized to send messages in this chat.',
            ], 403);
        }

        $message = ChatMessage::create([
            'chat_id' => $request->chat_id,
            'messages' => $request->messages,
            'sent_by' => $senderId,
            'sent_at' => now(),
        ]);

        broadcast(new MessageSent($message))->toOthers();

        $recipientIds = UserChat::where('chat_id', $request->chat_id)
            ->where('user_id', '!=', $senderId)
            ->pluck('user_id');

        foreach ($recipientIds as $recipientId) {
            $recipient = \App\Models\User::find($recipientId);
            if ($recipient) {
                $recipient->notify(new ChatPushNotification(
                    'New Message',
                    $request->messages,
                    $recipient->id
                ));

                ChatNotification::create([
                    'chat_id' => $request->chat_id,
                    'recipient_id' => $recipientId,
                    'sender_id' => $senderId,
                    'title' => 'New Message',
                    'content' => $request->messages,
                ]);
            }
        }

        return response()->json([
            'message' => 'Message sent and notified',
            'data' => $message,
        ]);
    }

    public function fetchMessages(Request $request, $chatId)
    {
        $userId = auth()->id();
        $isParticipant = UserChat::where('chat_id', $chatId)
            ->where('user_id', $userId)
            ->exists();

        if (! $isParticipant) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $perPage = 20;

        $messages = ChatMessage::where('chat_id', $chatId)
            ->orderBy('sent_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'chat_id' => $chatId,
            'messages' => $messages->items(),
            'current_page' => $messages->currentPage(),
            'last_page' => $messages->lastPage(),
            'per_page' => $messages->perPage(),
            'total' => $messages->total(),
        ]);
    }
}
