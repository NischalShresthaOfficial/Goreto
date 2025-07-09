<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupLocation;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location_ids' => 'required|array',
            'location_ids.*' => 'exists:locations,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $group = Group::create([
            'name' => $request->group_name,
            'description' => $request->description,
            'created_by' => Auth::id(),
        ]);

        UserGroup::create([
            'user_id' => Auth::id(),
            'group_id' => $group->id,
            'member_role' => 'admin',
        ]);

        foreach ($request->location_ids as $locationId) {
            GroupLocation::create([
                'group_id' => $group->id,
                'location_id' => $locationId,
            ]);
        }

        return response()->json([
            'message' => 'Group created successfully',
            'group_id' => $group->id,
        ]);
    }

    public function addMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $adminCheck = UserGroup::where('group_id', $request->group_id)
            ->where('user_id', Auth::id())
            ->where('member_role', 'admin')
            ->first();

        if (!$adminCheck) {
            return response()->json(['message' => 'Only group admins can add members.'], 403);
        }

        $exists = UserGroup::where('group_id', $request->group_id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($exists) {
            return response()->json(['message' => 'User is already a member of the group.'], 409);
        }

        UserGroup::create([
            'group_id' => $request->group_id,
            'user_id' => $request->user_id,
            'member_role' => 'member',
        ]);

        return response()->json(['message' => 'User added to the group.']);
    }

    public function members($groupId)
    {
        $members = UserGroup::with('user:id,name,email')
            ->where('group_id', $groupId)
            ->get(['user_id', 'member_role']);

        return response()->json($members);
    }

    public function removeMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => 'required|exists:groups,id',
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $adminCheck = UserGroup::where('group_id', $request->group_id)
            ->where('user_id', Auth::id())
            ->where('member_role', 'admin')
            ->first();

        if (!$adminCheck) {
            return response()->json(['message' => 'Only group admins can remove members.'], 403);
        }

        $membership = UserGroup::where('group_id', $request->group_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$membership) {
            return response()->json(['message' => 'User is not a member of the group.'], 404);
        }

        $membership->delete();

        return response()->json(['message' => 'User removed from the group.']);
    }
}

