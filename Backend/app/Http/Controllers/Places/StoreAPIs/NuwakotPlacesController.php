<?php

namespace App\Http\Controllers\Places\StoreAPIs;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Location;
use App\Models\LocationImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NuwakotPlacesController extends Controller
{
    public function fetchNuwakotPopularPlaces()
    {
        $apiKey = config('services.google_maps.key');

        ini_set('max_execution_time', 600);

        $location = '27.8780,85.1440';
        $radius = 50000;

        $allPlaces = [];
        $nextPageToken = null;
        $page = 0;

        $city = City::firstOrCreate(['city' => 'Nuwakot']);

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

            if (! $response->successful() || ! isset($data['results'])) {
                return response()->json(['message' => 'Failed to fetch places', 'error' => $data], 500);
            }

            foreach ($data['results'] as $place) {
                $placeId = $place['place_id'];
                if (! isset($allPlaces[$placeId])) {
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

            $description = $this->getWikipediaDescription($name);

            $locationModel = Location::where('place_id', $placeId)->first();

            if ($locationModel) {
                if (empty($locationModel->description) && $description) {
                    $locationModel->update(['description' => $description]);
                }
            } else {
                $locationModel = Location::create([
                    'place_id' => $placeId,
                    'name' => $name,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'description' => $description,
                    'category_id' => $category->id,
                    'city_id' => $city->id,
                ]);
            }

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
            'message' => 'Popular places across Nuwakot fetched and stored successfully.',
            'total_places' => count($allPlaces),
        ]);
    }

    private function getWikipediaDescription($name)
    {
        try {
            $cleanName = preg_replace('/\(.+\)/', '', $name);
            $cleanName = trim($cleanName);

            $searchTerms = [
                $cleanName,
                $cleanName.' Nepal',
                $cleanName.' Nuwakot',
            ];

            foreach ($searchTerms as $searchTerm) {
                $description = $this->searchWikipedia($searchTerm);
                if ($description) {
                    return $description;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Wikipedia description fetch failed for {$name}: ".$e->getMessage());

            return null;
        }
    }

    private function searchWikipedia($searchTerm)
    {
        try {
            $searchResponse = Http::withHeaders([
                'User-Agent' => 'YourAppName/1.0 (your-email@example.com)',
                'Accept' => 'application/json',
            ])->timeout(10)->get('https://en.wikipedia.org/api/rest_v1/page/summary/'.urlencode(str_replace(' ', '_', $searchTerm)));

            if ($searchResponse->successful()) {
                $data = $searchResponse->json();

                if (isset($data['extract']) &&
                    ! empty($data['extract']) &&
                    (! isset($data['type']) || $data['type'] !== 'disambiguation') &&
                    $data['extract'] !== 'Coordinates: ') {
                    return $data['extract'];
                }
            }

            $searchApiResponse = Http::withHeaders([
                'User-Agent' => 'YourAppName/1.0 (your-email@example.com)',
                'Accept' => 'application/json',
            ])->timeout(10)->get('https://en.wikipedia.org/w/api.php', [
                'action' => 'query',
                'format' => 'json',
                'list' => 'search',
                'srsearch' => $searchTerm,
                'srlimit' => 1,
            ]);

            if ($searchApiResponse->successful()) {
                $searchData = $searchApiResponse->json();

                if (isset($searchData['query']['search'][0]['title'])) {
                    $title = $searchData['query']['search'][0]['title'];

                    $summaryResponse = Http::withHeaders([
                        'User-Agent' => 'YourAppName/1.0 (your-email@example.com)',
                        'Accept' => 'application/json',
                    ])->timeout(10)->get('https://en.wikipedia.org/api/rest_v1/page/summary/'.urlencode(str_replace(' ', '_', $title)));

                    if ($summaryResponse->successful()) {
                        $summaryData = $summaryResponse->json();

                        if (isset($summaryData['extract']) &&
                            ! empty($summaryData['extract']) &&
                            (! isset($summaryData['type']) || $summaryData['type'] !== 'disambiguation')) {
                            return $summaryData['extract'];
                        }
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Wikipedia API error for '{$searchTerm}': ".$e->getMessage());

            return null;
        }
    }
}
