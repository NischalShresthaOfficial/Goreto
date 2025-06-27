<?php

namespace App\Http\Controllers\Places;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class UserCategoryController extends Controller
{
    public function searchByUserCategoryOnly(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'limit' => 'nullable|integer|min:1|max:50',
            'll' => 'required|string|regex:/^-?\d+\.\d+,-?\d+\.\d+$/',
        ]);

        $limit = $validated['limit'] ?? 10;
        $ll = $validated['ll'];

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

        $apiKey = env('FOURSQUARE_API_TOKEN');
        $categoriesParam = implode(',', $fsqCategoryIds);

        $queryParams = [
            'categories' => $categoriesParam,
            'limit' => $limit,
            'sort' => 'POPULARITY',
            'll' => $ll,
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'X-Places-API-Version' => '2025-06-17',
        ])->get('https://places-api.foursquare.com/places/search', $queryParams);

        if ($response->successful()) {
            return response()->json([
                'message' => 'Places fetched from Foursquare API by user categories and location',
                'data' => $response->json()['results'] ?? [],
            ]);
        }

        return response()->json([
            'message' => 'Failed to fetch places',
            'error' => $response->json(),
        ], $response->status());
    }
}
