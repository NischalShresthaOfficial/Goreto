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
            'radius' => ['nullable', 'integer', 'min:5000', 'max:50000'],
            'limit' => ['nullable', 'integer', 'min:5', 'max:10'],
            'category' => ['nullable', 'string'],
        ]);

        $latitude = $request->query('latitude');
        $longitude = $request->query('longitude');
        $radius = $request->query('radius', 5000);
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
            ->orderBy('distance');

        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('category', 'LIKE', "%$category%");
            });
        }

        $locations = $query->paginate($limit);

        return response()->json([
            'message' => 'Popular places fetched from local database',
            'total' => $locations->total(),
            'per_page' => $locations->perPage(),
            'current_page' => $locations->currentPage(),
            'last_page' => $locations->lastPage(),
            'count' => $locations->count(),
            'data' => $locations->items(),
        ]);

    }

    public function fetchById($id)
    {
        $location = Location::with([
            'locationImages' => function ($query) {
                $query->where('status', 'verified');
            },
            'category',
            'city',
        ])->find($id);

        if (! $location) {
            return response()->json([
                'message' => 'Location not found',
            ], 404);
        }

        return response()->json([
            'message' => 'Location fetched successfully',
            'data' => $location,
        ]);
    }
}
