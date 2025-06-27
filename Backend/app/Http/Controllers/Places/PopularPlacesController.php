<?php

namespace App\Http\Controllers\Places;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PopularPlacesController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'll' => ['required', 'regex:/^-?\d{1,3}\.\d+,-?\d{1,3}\.\d+$/'],
            'limit' => ['nullable', 'integer'],
            'radius' => ['nullable', 'integer'],
        ]);

        $limit = $validated['limit'] ?? 10;
        $radius = $validated['radius'] ?? 500;

        [$lat, $lng] = explode(',', $validated['ll']);

        $localResults = Location::with('locationImages')
            ->whereBetween('latitude', [(float) $lat - 0.5, (float) $lat + 0.5])
            ->whereBetween('longitude', [(float) $lng - 0.5, (float) $lng + 0.5])
            ->limit($limit)
            ->get();

        if ($localResults->isNotEmpty()) {
            return response()->json([
                'message' => 'Places fetched from database',
                'data' => $localResults,
            ]);
        }

        $apiKey = env('FOURSQUARE_API_TOKEN');

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'X-Places-API-Version' => '2025-06-17',
        ])->get('https://places-api.foursquare.com/places/search', [
            'll' => $validated['ll'],
            'radius' => $radius,
            'sort' => 'POPULARITY',
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            return response()->json([
                'message' => 'Places fetched from Foursquare API',
                'data' => $response->json(),
            ]);
        }

        return response()->json([
            'message' => 'Failed to fetch places',
            'error' => $response->json(),
        ], $response->status());
    }
}
