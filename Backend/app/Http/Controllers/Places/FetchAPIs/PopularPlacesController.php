<?php

namespace App\Http\Controllers\Places\FetchAPIs;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class PopularPlacesController extends Controller
{
    public function fetchFromDb(Request $request)
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'radius' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
            'category' => ['nullable', 'string'],
        ]);

        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');
        $radius = $request->query('radius', 500);
        $limit = $request->query('limit', 5);
        $category = $request->query('category');

        $radiusInDegrees = $radius / 111000;

        $query = Location::with([
            'locationImages' => function ($query) {
                $query->where('status', 'verified');
            },
            'category',
        ])
            ->selectRaw('
            *,
            (6371000 * acos(
                cos(radians(?)) *
                cos(radians(latitude)) *
                cos(radians(longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(latitude))
            )) AS distance', [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radius)
            ->orderBy('distance')
            ->limit($limit);

        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('category', 'LIKE', "%$category%");
            });
        }

        $locations = $query->get();

        return response()->json([
            'message' => 'Popular places fetched from local database',
            'count' => $locations->count(),
            'data' => $locations,
        ]);
    }
}
