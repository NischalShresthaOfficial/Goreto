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

    public function index(Request $request)
    {
        $user = Auth::user();

        $limit = $request->query('limit', 10);

        $favourites = FavouriteLocation::with(['location.locationImages', 'location.city', 'location.category'])
            ->where('user_id', $user->id)
            ->paginate($limit);

        $locations = $favourites->getCollection()->pluck('location');

        $paginatedLocations = new \Illuminate\Pagination\LengthAwarePaginator(
            $locations,
            $favourites->total(),
            $favourites->perPage(),
            $favourites->currentPage(),
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return response()->json([
            'message' => 'Favourite locations fetched successfully.',
            'total' => $paginatedLocations->total(),
            'per_page' => $paginatedLocations->perPage(),
            'current_page' => $paginatedLocations->currentPage(),
            'last_page' => $paginatedLocations->lastPage(),
            'count' => $paginatedLocations->count(),
            'data' => $paginatedLocations->items(),
        ]);
    }
}
