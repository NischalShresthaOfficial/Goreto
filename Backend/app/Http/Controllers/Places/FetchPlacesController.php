<?php

namespace App\Http\Controllers\Places;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class FetchPlacesController extends Controller
{
    public function fetchNepalPlaces(Request $request)
    {
        $limit = $request->input('limit', 50);

        $minLat = 26.347;
        $maxLat = 30.446;
        $minLong = 80.058;
        $maxLong = 88.201;

        $places = Location::whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLong, $maxLong])
            ->limit($limit)
            ->get();

        return response()->json([
            'message' => 'Popular places in Nepal fetched successfully',
            'data' => $places,
        ]);
    }
}
