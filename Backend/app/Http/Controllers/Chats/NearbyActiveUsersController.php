<?php

namespace App\Http\Controllers\Chats;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class NearbyActiveUsersController extends Controller
{
    public function fetchNearbyOnlineUsers(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|integer|min:1|max:50000',
        ]);

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $radius = $request->radius ?? 1000;

        $userId = auth()->id();

        $users = User::where('activity_status', true)
            ->where('users.id', '!=', $userId)
            ->join('user_locations', 'users.id', '=', 'user_locations.user_id')
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.created_at',
                'users.updated_at',
                'users.country_id',
            ])
            ->selectRaw('(6371000 * acos(
            cos(radians(?)) *
            cos(radians(user_locations.latitude)) *
            cos(radians(user_locations.longitude) - radians(?)) +
            sin(radians(?)) *
            sin(radians(user_locations.latitude))
        )) AS distance', [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->get();

        return response()->json([
            'message' => 'Nearby online users fetched successfully',
            'count' => $users->count(),
            'data' => $users,
        ]);
    }
}
