<?php

namespace App\Http\Controllers\Places\StoreAPIs;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Location;
use App\Models\LocationImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BhaktapurPlacesController extends Controller
{
    public function fetchBhaktapurPopularPlaces()
    {
        $apiKey = config('services.google_maps.key');

        ini_set('max_execution_time', 300);

        $location = '27.6710,85.4298';
        $radius = 50000;

        $allPlaces = [];
        $nextPageToken = null;
        $page = 0;

        $city = City::firstOrCreate(['city' => 'Bhaktapur']);

        do {
            $params = [
                'location' => $location,
                'radius' => $radius,
                'key' => $apiKey,
                'type' => 'point_of_interest',
            ];

            if ($nextPageToken) {
                $params['pagetoken'] = $nextPageToken;
                sleep(2);
            }

            $response = Http::get('https://maps.googleapis.com/maps/api/place/nearbysearch/json', $params);
            $data = $response->json();

            if (!$response->successful() || !isset($data['results'])) {
                return response()->json(['message' => 'Failed to fetch places', 'error' => $data], 500);
            }

            foreach ($data['results'] as $place) {
                $placeId = $place['place_id'];
                if (!isset($allPlaces[$placeId])) {
                    $allPlaces[$placeId] = $place;
                }
            }

            $nextPageToken = $data['next_page_token'] ?? null;
            $page++;

        } while ($nextPageToken && $page < 3);

        foreach ($allPlaces as $place) {
            $lat = $place['geometry']['location']['lat'];
            $lng = $place['geometry']['location']['lng'];
            $name = $place['name'];
            $placeId = $place['place_id'];
            $categoryName = $place['types'][0] ?? 'General';

            $category = Category::firstOrCreate([
                'category' => Str::title(str_replace('_', ' ', $categoryName)),
            ]);

            $locationModel = Location::updateOrCreate(
                ['place_id' => $placeId],
                [
                    'name' => $name,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'category_id' => $category->id,
                    'city_id' => $city->id,
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
                    $filename = 'locations/' . Str::uuid() . '.jpg';
                    Storage::disk('public')->put($filename, $photoResponse->body());

                    LocationImage::firstOrCreate([
                        'location_id' => $locationModel->id,
                        'image_path' => $filename,
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Popular places across Bhaktapur fetched and stored successfully.',
            'total_places' => count($allPlaces),
        ]);
    }
}
