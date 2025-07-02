<?php

namespace App\Http\Controllers\Places\CategoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserCategoryController extends Controller
{
    public function fetchByUserCategories(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $limit = $request->query('limit', 20);

        $categoryIds = DB::table('user_categories')
            ->where('user_id', $user->id)
            ->pluck('category_id')
            ->toArray();

        if (empty($categoryIds)) {
            return response()->json([
                'message' => 'No categories found for user',
                'data' => [],
            ]);
        }

        $places = Location::with([
            'locationImages' => function ($query) {
                $query->where('status', 'verified');
            },
            'category',
        ])
            ->whereIn('category_id', $categoryIds)
            ->paginate($limit);

        return response()->json([
            'message' => 'Places fetched based on user categories',
            'total' => $places->total(),
            'per_page' => $places->perPage(),
            'current_page' => $places->currentPage(),
            'last_page' => $places->lastPage(),
            'count' => $places->count(),
            'data' => $places->items(),
        ]);
    }

    public function fetchById($id)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $categoryIds = DB::table('user_categories')
            ->where('user_id', $user->id)
            ->pluck('category_id')
            ->toArray();

        $location = Location::with([
            'locationImages' => function ($query) {
                $query->where('status', 'verified');
            },
            'category',
            'city',
        ])
            ->where('id', $id)
            ->whereIn('category_id', $categoryIds)
            ->first();

        if (! $location) {
            return response()->json([
                'message' => 'Location not found or not in your categories',
            ], 404);
        }

        return response()->json([
            'message' => 'Place fetched successfully',
            'data' => $location,
        ]);
    }
}
