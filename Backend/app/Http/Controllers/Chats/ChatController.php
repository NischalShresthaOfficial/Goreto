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

    public function createGroupChat(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id|different:'.auth()->id(),
        ]);

        $currentUserId = auth()->id();

        $userIds = array_unique(array_merge([$currentUserId], $request->user_ids));

        $chat = Chat::create([
            'name' => $request->name,
            'is_group' => true,
            'created_by' => $currentUserId,
        ]);

        $chat->users()->attach($userIds);

        return response()->json([
            'message' => 'Group chat created successfully',
            'chat' => $chat->load('users'),
        ], 201);
    }

    public function viewMembers($chatId)
    {
        $chat = Chat::where('id', $chatId)->where('is_group', true)->firstOrFail();

        if (! $chat->users()->where('user_id', auth()->id())->exists()) {
            return response()->json(['message' => 'Unauthorized to view members'], 403);
        }

        $members = $chat->users()
            ->select('users.id', 'users.name', 'users.email')
            ->get();

        return response()->json([
            'chat_id' => $chat->id,
            'chat_name' => $chat->name,
            'members' => $members,
        ]);
    }

    public function addMember(Request $request, $chatId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|different:'.auth()->id(),
        ]);

        $chat = Chat::where('id', $chatId)->where('is_group', true)->firstOrFail();

        if ((int) $chat->created_by !== (int) auth()->id()) {
            return response()->json(['message' => 'Only group owner can manage members'], 403);
        }

        $userIdToAdd = $request->user_id;

        if ($chat->users()->where('user_id', $userIdToAdd)->exists()) {
            return response()->json(['message' => 'User already in the group'], 400);
        }

        $chat->users()->attach($userIdToAdd);

        return response()->json([
            'message' => 'User added to group successfully',
            'chat' => $chat->load('users'),
        ]);
    }

    public function removeMember(Request $request, $chatId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|different:'.auth()->id(),
        ]);

        $chat = Chat::where('id', $chatId)->where('is_group', true)->firstOrFail();

        if ((int) $chat->created_by !== (int) auth()->id()) {
            return response()->json(['message' => 'Only group owner can manage members'], 403);
        }

        $userIdToRemove = $request->user_id;

        if ($userIdToRemove == auth()->id()) {
            return response()->json(['message' => 'You cannot remove yourself'], 400);
        }

        if (! $chat->users()->where('user_id', $userIdToRemove)->exists()) {
            return response()->json(['message' => 'User not in the group'], 400);
        }

        $chat->users()->detach($userIdToRemove);

        return response()->json([
            'message' => 'User removed from group successfully',
            'chat' => $chat->load('users'),
        ]);
    }

    public function deleteGroupChat($chatId)
    {
        $chat = Chat::where('id', $chatId)->where('is_group', true)->firstOrFail();

        if ((int) $chat->created_by !== (int) auth()->id()) {
            return response()->json(['message' => 'Only group owner can manage members'], 403);
        }

        $chat->delete();

        return response()->json([
            'message' => 'Group chat deleted successfully',
        ]);
    }
}
