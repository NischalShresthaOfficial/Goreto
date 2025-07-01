<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\FavouriteLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavouriteLocationController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
        ]);

        $user = Auth::user();

        $favourite = FavouriteLocation::firstOrCreate([
            'user_id' => $user->id,
            'location_id' => $request->location_id,
        ]);

        return response()->json([
            'message' => 'Location added to favourites successfully.',
            'data' => $favourite,
        ]);
    }

    public function index()
    {
        $user = Auth::user();

        $favourites = FavouriteLocation::with(['location.locationImages', 'location.city', 'location.category'])
            ->where('user_id', $user->id)
            ->get()
            ->pluck('location');

        return response()->json([
            'message' => 'Favourite locations fetched successfully.',
            'count' => $favourites->count(),
            'data' => $favourites,
        ]);
    }
}
