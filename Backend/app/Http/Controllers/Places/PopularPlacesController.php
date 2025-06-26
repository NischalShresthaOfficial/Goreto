<?php

namespace App\Http\Controllers\Places;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PopularPlacesController extends Controller
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'll' => ['required', 'regex:/^-?\d{1,3}\.\d+,-?\d{1,3}\.\d+$/'],
        ]);

        $apiKey = env('FOURSQUARE_API_TOKEN');

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'X-Places-API-Version' => '2025-06-17',
        ])->get('https://places-api.foursquare.com/places/search', [
            'll' => $validated['ll'],
            'radius' => 100000,
            'sort' => 'POPULARITY',
            'limit' => 10,
        ]);

        if ($response->successful()) {
            return response()->json([
                'message' => 'Places fetched successfully',
                'data' => $response->json(),
            ]);
        }

        return response()->json([
            'message' => 'Failed to fetch places',
            'error' => $response->json(),
        ], $response->status());
    }
}
