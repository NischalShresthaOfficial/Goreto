<?php

namespace App\Http\Controllers\Locations;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RecommendationController extends Controller
{
    private function detectCategories(string $keywords): array
    {
        $dbCategories = Category::pluck('category')->toArray();
        $matchedCategories = [];

        $keywords = strtolower($keywords);

        foreach ($dbCategories as $dbCategory) {
            if (str_contains($keywords, strtolower($dbCategory))) {
                $matchedCategories[] = $dbCategory;
            }
        }

        if (empty($matchedCategories)) {
            foreach ($dbCategories as $dbCategory) {
                $categoryWords = explode(' ', strtolower($dbCategory));
                foreach ($categoryWords as $word) {
                    if (strlen($word) > 3 && str_contains($keywords, $word)) {
                        $matchedCategories[] = $dbCategory;
                        break;
                    }
                }
            }
        }

        return array_unique($matchedCategories);
    }

    public function recommendFromPrompt(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:500',
        ]);

        $promptText = $request->prompt;

        $keywords = strtolower($promptText);
        $categoryFilter = $this->detectCategories($keywords);

        $locations = Location::with(['category', 'city'])
            ->when(!empty($categoryFilter), function ($query) use ($categoryFilter) {
                $query->whereHas('category', function ($q) use ($categoryFilter) {
                    $q->whereIn('category', $categoryFilter);
                });
            })
            ->limit(10)
            ->get();

        if ($locations->isEmpty()) {
            return response()->json([
                'message' => 'No relevant locations found in database.',
                'recommendations' => [],
            ]);
        }

        $locationList = $locations->map(function ($loc) {
            return "{$loc->name} in {$loc->city->city} ({$loc->category->category}) - Rating: " . ($loc->average_rating ?? 'N/A');
        })->implode("\n");

        $finalPrompt = "User request: \"{$promptText}\"\n\nAvailable locations:\n{$locationList}\n\nRecommend exactly 5 most suitable locations from the list above, ranked by relevance to the request. Consider factors like ratings, location convenience, and category match. Provide brief explanations for each recommendation using only the provided information. Format as numbered list.";

        $apiKey = config('services.cohere.api_key');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
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

        return response()->json([
            'message' => 'Recommendations fetched successfully.',
            'recommendations' => $recommendationText,
            'matched_categories' => $categoryFilter,
        ]);
    }
}
