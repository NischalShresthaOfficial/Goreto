<?php

namespace App\Http\Controllers\Chats;

use App\Http\Controllers\Controller;
use App\Models\UserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLocationController extends Controller
{
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();

        $location = UserLocation::updateOrCreate(
            ['user_id' => $user->id],
            ['latitude' => $request->latitude, 'longitude' => $request->longitude]
        );

        return response()->json([
            'message' => 'Location updated successfully.',
            'data' => $location,
        ]);
    }
}
