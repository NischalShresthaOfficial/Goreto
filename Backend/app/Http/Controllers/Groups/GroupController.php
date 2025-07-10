<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
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

        UserGroup::create([
            'user_id' => $user->id,
            'group_id' => $group->id,
            'member_role' => 'admin',
        ]);

        return response()->json([
            'message' => 'Group created successfully.',
            'group' => $group,
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

        return response()->json([
            'message' => 'Joined group successfully.',
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
}
