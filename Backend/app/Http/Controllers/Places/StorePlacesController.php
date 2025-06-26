<?php

namespace App\Http\Controllers\Places;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StorePlacesController extends Controller
{
    public function fetchAndStore(Request $request)
    {
        $limit = $request->input('limit', 10);
        if ($limit > 100) {
            $limit = 100;
        }

        $coords = [
            '29.0000,80.5000', // Darchula
            '28.5000,81.2500', // Baitadi / Dadeldhura
            '28.0000,82.0000', // Rukum / Salyan
            '27.7000,83.0000', // Butwal / Rupandehi
            '27.7000,85.3000', // Kathmandu
            '28.2000,83.9856', // Pokhara
            '27.4000,84.5000', // Chitwan
            '26.8000,87.2000', // Biratnagar / Sunsari
            '27.0000,88.0000', // Ilam / Jhapa
            '28.7000,85.5000', // Rasuwa / Langtang
            '29.3000,83.9000', // Mustang
        ];

        $apiKey = env('FOURSQUARE_API_TOKEN');
        $headers = [
            'Authorization' => "Bearer {$apiKey}",
            'X-Places-API-Version' => '2025-06-17',
        ];

        $allPlaces = [];

        foreach ($coords as $ll) {
            $response = Http::withHeaders($headers)->get('https://places-api.foursquare.com/places/search', [
                'll' => $ll,
                'radius' => 50000,
                'sort' => 'POPULARITY',
                'limit' => 10,
            ]);

            if ($response->successful()) {
                $places = $response->json()['results'] ?? [];

                foreach ($places as $place) {
                    $name = trim($place['name'] ?? 'Unknown');
                    $latitude = $place['latitude'] ?? null;
                    $longitude = $place['longitude'] ?? null;
                    $locality = $place['location']['locality'] ?? null;

                    if (! $locality || ! $latitude || ! $longitude) {
                        continue;
                    }

                    $city = City::firstOrCreate(['city' => $locality]);
                    $cityId = $city->id;

                    $exists = Location::whereRaw('LOWER(name) = ?', [strtolower($name)])
                        ->where('latitude', $latitude)
                        ->where('longitude', $longitude)
                        ->exists();

                    if (! $exists) {
                        $locationData = [
                            'name' => $name,
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                            'city_id' => $cityId,
                        ];

                        $allPlaces[] = Location::create($locationData);
                    }
                }
            }
        }

        return response()->json([
            'message' => 'Fetched and stored popular places in Nepal.',
            'stored_count' => count($allPlaces),
            'places' => $allPlaces,
        ]);
    }
}
