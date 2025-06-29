<?php

namespace App\Http\Controllers\Places;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use App\Models\LocationImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    public function fetchPopularPlaces(Request $request)
    {
        $request->validate([
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'radius' => ['nullable', 'integer', 'min:1', 'max:50000'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:60'],
            'category' => ['nullable', 'string'],
        ]);

        $apiKey = config('services.google_maps.key');

        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $radius = $request->input('radius', 3000);
        $limit = $request->input('limit', 20);
        $category = $request->input('category', 'point_of_interest');

        $allPlaces = [];
        $nextPageToken = null;
        $page = 0;

        do {
            $params = [
                'location' => "{$latitude},{$longitude}",
                'radius' => $radius,
                'key' => $apiKey,
                'type' => $category,
            ];

            if ($nextPageToken) {
                $params['pagetoken'] = $nextPageToken;
                sleep(2);
            }

            $response = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', $params);
            $data = $response->json();

            if (! $response->successful() || ! isset($data['results'])) {
                return response()->json(['message' => 'Failed to fetch places', 'error' => $data], 500);
            }

            foreach ($data['results'] as $place) {
                $placeId = $place['place_id'];
                if (! isset($allPlaces[$placeId]) && count($allPlaces) < $limit) {
                    $allPlaces[$placeId] = $place;
                }
            }

            $nextPageToken = $data['next_page_token'] ?? null;
            $page++;

        } while ($nextPageToken && count($allPlaces) < $limit && $page < 3);

        foreach ($allPlaces as $place) {
            $lat = $place['geometry']['location']['lat'];
            $lng = $place['geometry']['location']['lng'];
            $name = $place['name'];
            $placeId = $place['place_id'];
            $categoryName = $place['types'][0] ?? 'General';

            $categoryModel = Category::firstOrCreate([
                'category' => Str::title(str_replace('_', ' ', $categoryName)),
            ]);

            $locationModel = Location::updateOrCreate(
                ['place_id' => $placeId],
                [
                    'name' => $name,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'category_id' => $categoryModel->id,
                ]
            );

            if (isset($place['photos'][0]['photo_reference'])) {
                $photoRef = $place['photos'][0]['photo_reference'];

                $photoResponse = Http::get('https://maps.googleapis.com/maps/api/place/photo', [
                    'photoreference' => $photoRef,
                    'maxwidth' => 800,
                    'key' => $apiKey,
                ]);

                if ($photoResponse->successful()) {
                    $filename = 'locations/'.Str::uuid().'.jpg';
                    Storage::disk('public')->put($filename, $photoResponse->body());

                    LocationImage::firstOrCreate([
                        'location_id' => $locationModel->id,
                        'image_path' => $filename,
                    ]);
                }
            }
        }

        return response()->json([
            'message' => "Fetched and stored {$limit} popular places around ({$latitude}, {$longitude})",
            'total_places' => count($allPlaces),
        ]);
    }
}
