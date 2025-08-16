<?php

namespace App\Http\Controllers\Locations;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RecommendationController extends Controller
{
    private function detectCategoriesFast(string $userPrompt): array
    {
        $dbCategories = Category::pluck('category')->toArray();
        $prompt = strtolower($userPrompt);
        $matchedCategories = [];

        $intentMap = $this->createDynamicIntentMap($dbCategories);

        foreach ($intentMap as $keywords => $possibleCategories) {
            $keywordList = explode('|', $keywords);
            $score = 0;

            foreach ($keywordList as $keyword) {
                if (str_contains($prompt, trim($keyword))) {
                    $score++;
                }
            }

            if ($score > 0) {
                foreach ($possibleCategories as $category) {
                    if (! isset($matchedCategories[$category])) {
                        $matchedCategories[$category] = 0;
                    }
                    $matchedCategories[$category] += $score;
                }
            }
        }

        foreach ($dbCategories as $category) {
            if (str_contains($prompt, strtolower($category))) {
                if (! isset($matchedCategories[$category])) {
                    $matchedCategories[$category] = 0;
                }
                $matchedCategories[$category] += 10;
            }
        }

        foreach ($dbCategories as $category) {
            $categoryWords = explode(' ', strtolower($category));
            foreach ($categoryWords as $word) {
                if (strlen($word) > 3 && str_contains($prompt, $word)) {
                    if (! isset($matchedCategories[$category])) {
                        $matchedCategories[$category] = 0;
                    }
                    $matchedCategories[$category] += 5;
                }
            }
        }

        if (! empty($matchedCategories)) {
            arsort($matchedCategories);

            return array_keys(array_slice($matchedCategories, 0, 5));
        }

        return [];
    }

    private function createDynamicIntentMap(array $dbCategories): array
    {
        $baseIntentMap = [
            'date|romantic|couple|anniversary|special occasion|intimate|cozy' => ['restaurant', 'cafe', 'hotel', 'bar', 'dining', 'accommodation'],

            'view|scenery|ambiance|atmosphere|beautiful|nice place|stunning|panoramic' => ['restaurant', 'hotel', 'cafe', 'rooftop', 'terrace', 'accommodation'],

            'eat|food|meal|dinner|lunch|breakfast|dine|hungry|cuisine' => ['restaurant', 'cafe', 'dining', 'food'],

            'drink|cocktail|wine|beer|evening|night out|bar|pub' => ['bar', 'pub', 'lounge', 'nightlife', 'drinks'],

            'coffee|tea|casual|study|work|meeting|chat' => ['cafe', 'coffee shop', 'coffee'],

            'stay|sleep|hotel|room|accommodation|getaway|vacation' => ['hotel', 'resort', 'lodge', 'accommodation'],

            'shop|buy|mall|store|purchase|market|retail' => ['shopping', 'mall', 'market', 'store', 'retail'],

            'fun|entertainment|activity|movie|cinema|game|recreation' => ['entertainment', 'cinema', 'recreation', 'activities'],

            'relax|spa|massage|wellness|beauty|therapy|peaceful' => ['spa', 'wellness', 'beauty', 'massage'],

            'nature|park|outdoor|hiking|walk|garden|fresh air' => ['park', 'garden', 'outdoor', 'nature'],

            'culture|museum|art|history|temple|spiritual|traditional' => ['museum', 'temple', 'cultural', 'gallery', 'heritage'],
        ];

        $filteredIntentMap = [];
        foreach ($baseIntentMap as $keywords => $potentialCategories) {
            $existingCategories = [];

            foreach ($potentialCategories as $category) {
                foreach ($dbCategories as $dbCategory) {
                    if (strtolower($dbCategory) === strtolower($category)) {
                        $existingCategories[] = $dbCategory;
                        break;
                    }
                }
            }

            if (! empty($existingCategories)) {
                $filteredIntentMap[$keywords] = array_unique($existingCategories);
            }
        }

        return $filteredIntentMap;
    }

    private function extractRecommendedLocations(string $recommendationText, $locations): array
    {
        $recommendedNames = [];
        $locationNames = $locations->pluck('name')->toArray();

        foreach ($locationNames as $locationName) {
            if (stripos($recommendationText, $locationName) !== false) {
                $recommendedNames[] = $locationName;
            }
        }

        return $recommendedNames;
    }

    public function recommendFromPrompt(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:500',
        ]);

        $promptText = $request->prompt;

        $categoryFilter = $this->detectCategoriesFast($promptText);

        $locations = Location::with(['category', 'city', 'locationImages'])
            ->when(! empty($categoryFilter), function ($query) use ($categoryFilter) {
                $query->whereHas('category', function ($q) use ($categoryFilter) {
                    $q->whereIn('category', $categoryFilter);
                });
            })
            ->limit(5)
            ->get();

        if ($locations->isEmpty()) {
            return response()->json([
                'message' => 'No relevant locations found in database.',
                'recommendations' => [],
                'locations' => [],
                'matched_categories' => $categoryFilter,
            ]);
        }

        $locationList = $locations->map(function ($loc) {
            return "{$loc->name} in {$loc->city->city} ({$loc->category->category}) - Rating: ".($loc->average_rating ?? 'N/A');
        })->implode("\n");

        $locationCount = $locations->count();
        $maxRecommendations = min(5, $locationCount);

        $finalPrompt = "User request: \"{$promptText}\"\n\nAvailable locations:\n{$locationList}\n\nRecommend the {$maxRecommendations} most suitable location".($maxRecommendations > 1 ? 's' : '').' from the list above, ranked by relevance to the request. Consider factors like ratings, location convenience, and category match. Provide brief explanations for each recommendation using only the provided information. Format as numbered list (1, 2, 3, etc.). IMPORTANT: Only recommend locations that are actually listed above - do not mention unavailable slots or create placeholder entries.';

        $apiKey = config('services.cohere.api_key');

        $response = Http::timeout(8)->withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.cohere.ai/v1/chat', [
            'model' => 'command-r-plus',
            'message' => $finalPrompt,
            'max_tokens' => 500,
            'temperature' => 0.3,
        ]);

        if ($response->failed()) {
            return response()->json([
                'message' => 'Failed to get recommendations from Cohere.',
                'status' => $response->status(),
                'body' => $response->body(),
            ], 500);
        }

        $cohereData = $response->json();
        $recommendationText = $cohereData['text'] ?? '';

        $recommendedLocationNames = $this->extractRecommendedLocations($recommendationText, $locations);

        $recommendedLocations = $locations->filter(function ($loc) use ($recommendedLocationNames) {
            return in_array($loc->name, $recommendedLocationNames);
        });

        $locationDetails = $recommendedLocations->map(function ($loc) {
            return [
                'id' => $loc->id,
                'name' => $loc->name,
                'latitude' => $loc->latitude,
                'longitude' => $loc->longitude,
                'city' => $loc->city->city ?? 'Unknown',
                'category' => $loc->category->category ?? 'Unknown',
                'description' => $loc->description ?? 'No description available',
                'average_rating' => $loc->average_rating,
                'place_id' => $loc->place_id ?? null,
                'distance' => $loc->distance ?? null,
                'images' => $loc->locationImages->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_url' => $image->image_url,
                        'status' => $image->status ?? 'unknown',
                    ];
                })->toArray(),
            ];
        });

        return response()->json([
            'message' => 'Recommendations fetched successfully.',
            'recommendations' => $recommendationText,
            'locations' => $locationDetails,
            'matched_categories' => $categoryFilter,
            'debug_info' => [
                'total_db_categories' => Category::count(),
                'available_categories' => Category::pluck('category')->toArray(),
            ],
        ]);
    }
}
