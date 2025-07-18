<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Group;
use App\Models\Payment;
use App\Models\UserGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    protected function hasActiveSubscription($userId): bool
    {
        return Payment::where('user_id', $userId)
            ->where('status', 'succeeded')
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = Auth::user();

        $groupCount = UserGroup::where('user_id', $user->id)->count();

        if (! $this->hasActiveSubscription($user->id) && $groupCount >= 5) {
            return response()->json([
                'message' => 'Group creation limit reached. Please subscribe to create more groups.',
            ], 403);
        }

        $group = Group::create([
            'name' => $request->name,
            'created_by' => $user->id,
        ]);

        $chat = Chat::create([
            'name' => $group->name,
            'is_group' => true,
            'created_by' => $user->id,
        ]);

        $group->group_chat_id = $chat->id;
        $group->save();

        UserGroup::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'member_role' => 'admin',
        ]);

        $chat->users()->attach($user->id);

        return response()->json([
            'message' => 'Group and group chat created successfully.',
            'group' => $group,
            'group_chat' => $chat,
        ], 201);
    }

    public function join(Request $request, $groupId)
    {
        $user = Auth::user();

        $group = Group::findOrFail($groupId);

        if ($group->userGroups()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'You are already a member of this group.',
            ], 400);
        }

        $groupCount = UserGroup::where('user_id', $user->id)->count();

        if (! $this->hasActiveSubscription($user->id) && $groupCount >= 5) {
            return response()->json([
                'message' => 'Group join limit reached. Please subscribe to join more groups.',
            ], 403);
        }

        UserGroup::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'member_role' => 'member',
        ]);

        if ($group->group_chat_id) {
            $chat = Chat::find($group->group_chat_id);
            if ($chat && ! $chat->users()->where('user_id', $user->id)->exists()) {
                $chat->users()->attach($user->id);
            }
        }

        return response()->json([
            'message' => 'Joined group and group chat successfully.',
            'group' => $group,
        ]);
    }

    public function addLocation(Request $request, Group $group)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
        ]);

        $user = Auth::user();
        $isMember = $group->userGroups()->where('user_id', $user->id)->exists();

        if (! $isMember) {
            return response()->json([
                'message' => 'You are not a member of this group.',
            ], 403);
        }

        $exists = $group->groupLocations()->where('location_id', $request->location_id)->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Location already added to the group.',
            ], 400);
        }

        $groupLocation = $group->groupLocations()->create([
            'location_id' => $request->location_id,
        ]);

        return response()->json([
            'message' => 'Location added to group successfully.',
            'group_location' => $groupLocation,
        ], 201);
    }

    public function index()
    {
        $user = Auth::user();

        $groups = Group::whereHas('userGroups', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['userGroups.user', 'groupLocations.location'])->get();

        return response()->json([
            'groups' => $groups,
        ]);
    }

    public function myGroups()
    {
        $user = Auth::user();

        $groups = Group::whereHas('userGroups', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->orWhere('created_by', $user->id)
            ->with(['userGroups.user', 'groupLocations.location'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'groups' => $groups,
        ]);
    }

    public function show($groupId)
    {
        $user = Auth::user();

        $group = Group::where('id', $groupId)
            ->whereHas('userGroups', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['userGroups.user', 'groupLocations.location'])
            ->first();

        if (! $group) {
            return response()->json([
                'message' => 'Group not found or access denied.',
            ], 404);
        }

        return response()->json([
            'group' => $group,
        ]);
    }

    public function myGroupsById($groupId)
    {
        $user = Auth::user();

        $group = Group::where('id', $groupId)
            ->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                    ->orWhereHas('userGroups', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    });
            })
            ->with(['userGroups.user', 'groupLocations.location'])
            ->first();

        if (! $group) {
            return response()->json([
                'message' => 'Group not found or access denied.',
            ], 404);
        }

        return response()->json([
            'group' => $group,
        ]);
    }
}
