<?php

namespace App\Http\Controllers\Places;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Location;
use App\Models\LocationImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StorePlacesController extends Controller
{
    public function fetchAndStore(Request $request)
    {
        ini_set('max_execution_time', 900);

        $apiKey = env('FOURSQUARE_API_TOKEN');
        $headers = [
            'Authorization' => "Bearer {$apiKey}",
            'X-Places-API-Version' => '2025-06-17',
        ];

        $minLat = 26.4; // Southern Nepal
        $maxLat = 30.5; // Northern Nepal
        $minLng = 80.0; // Western Nepal
        $maxLng = 88.2; // Eastern Nepal

        $latStep = 0.3;
        $lngStep = 0.3;

        $allPlaces = [];

        for ($lat = $minLat; $lat <= $maxLat; $lat += $latStep) {
            for ($lng = $minLng; $lng <= $maxLng; $lng += $lngStep) {
                $ll = "{$lat},{$lng}";

                $response = Http::withHeaders($headers)->get('https://places-api.foursquare.com/places/search', [
                    'll' => $ll,
                    'radius' => 10000,
                    'sort' => 'POPULARITY',
                    'limit' => 20,
                ]);

                if (! $response->successful()) {
                    Log::error("Foursquare API failed at: {$ll}");

                    continue;
                }

                $places = $response->json()['results'] ?? [];

                foreach ($places as $place) {
                    $name = trim($place['name'] ?? 'Unknown');
                    $latitude = $place['latitude'] ?? null;
                    $longitude = $place['longitude'] ?? null;
                    $locality = $place['location']['locality'] ?? null;

                    if (! $name || ! $latitude || ! $longitude || ! $locality) {
                        continue;
                    }

                    $latRounded = round($latitude, 5);
                    $lngRounded = round($longitude, 5);
                    $latLongKey = "{$latRounded},{$lngRounded}";

                    $exists = Location::where('lat_long', $latLongKey)->exists();

                    if ($exists) {
                        continue;
                    }

                    $city = City::firstOrCreate(['city' => $locality]);
                    $categoryId = null;

                    if (! empty($place['categories'])) {
                        $firstCategory = $place['categories'][0];
                        $fsqCategoryId = $firstCategory['id'] ?? null;
                        $categoryName = $firstCategory['name'] ?? 'Unknown';

                        if ($fsqCategoryId !== null) {
                            $category = Category::firstOrCreate(
                                ['fsq_category_id' => $fsqCategoryId],
                                ['category' => $categoryName]
                            );

                            $categoryId = $category->id;
                        } else {
                            $category = Category::firstOrCreate(['category' => $categoryName]);
                            $categoryId = $category->id;
                        }
                    }

                    $location = Location::create([
                        'name' => $name,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'lat_long' => $latLongKey,
                        'city_id' => $city->id,
                        'category_id' => $categoryId,
                    ]);

                    if (! empty($place['categories'])) {
                        foreach ($place['categories'] as $category) {
                            if (! empty($category['icon']['prefix']) && ! empty($category['icon']['suffix'])) {
                                $imageUrl = $category['icon']['prefix'].'64'.$category['icon']['suffix'];
                                $hash = md5($imageUrl);
                                $filename = 'location-images/'.$hash.'.png';

                                $existingImage = LocationImage::where('image_path', 'like', "%{$hash}%")->first();

                                if ($existingImage) {
                                    LocationImage::create([
                                        'location_id' => $location->id,
                                        'image_path' => $existingImage->image_path,
                                    ]);
                                } else {
                                    $imageContents = Http::get($imageUrl)->body();
                                    if (! Storage::disk('public')->exists($filename)) {
                                        Storage::disk('public')->put($filename, $imageContents);
                                    }

                                    LocationImage::create([
                                        'location_id' => $location->id,
                                        'image_path' => $filename,
                                    ]);
                                }
                            }
                        }
                    }

                    $allPlaces[] = $location;
                }

                usleep(300000);
            }
        }

        return response()->json([
            'message' => 'Fetched and stored places across Nepal.',
            'stored_count' => count($allPlaces),
            'places' => $allPlaces,
        ]);
    }
}
