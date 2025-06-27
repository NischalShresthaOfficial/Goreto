<?php

namespace App\Http\Controllers\Places;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UserCategoryController extends Controller
{
    public function searchAroundUser(Request $request)
    {
        $validated = $request->validate([
            'll' => ['required', 'regex:/^-?\d{1,3}\.\d+,-?\d{1,3}\.\d+$/'],
        ]);

        $limit = 10;
        $radius = 100000;

        [$lat, $lng] = explode(',', $validated['ll']);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userCategoryIds = DB::table('user_categories')
            ->where('user_id', $user->id)
            ->pluck('category_id')
            ->toArray();

        if (empty($userCategoryIds)) {
            return response()->json([
                'message' => 'No categories selected for user',
                'data' => [],
            ]);
        }

        $fsqCategoryIds = Category::whereIn('id', $userCategoryIds)
            ->pluck('fsq_category_id')
            ->filter()
            ->all();

        $localResults = Location::with('locationImages')
            ->whereBetween('latitude', [(float) $lat - 0.5, (float) $lat + 0.5])
            ->whereBetween('longitude', [(float) $lng - 0.5, (float) $lng + 0.5])
            ->whereIn('category_id', $userCategoryIds)
            ->limit($limit)
            ->get();

        if ($localResults->isNotEmpty()) {
            return response()->json([
                'message' => 'Places fetched from database',
                'data' => $localResults,
            ]);
        }

        $apiKey = env('FOURSQUARE_API_TOKEN');
        $categoriesParam = implode(',', $fsqCategoryIds);

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'X-Places-API-Version' => '2025-06-17',
        ])->get('https://places-api.foursquare.com/places/search', [
            'll' => $validated['ll'],
            'radius' => $radius,
            'sort' => 'POPULARITY',
            'limit' => $limit,
            'categories' => $categoriesParam,
        ]);

        if ($response->successful()) {
            return response()->json([
                'message' => 'Places fetched from Foursquare API',
                'data' => $response->json()['results'] ?? [],
            ]);
        }

        return response()->json([
            'message' => 'Failed to fetch places',
            'error' => $response->json(),
        ], $response->status());
    }
}
