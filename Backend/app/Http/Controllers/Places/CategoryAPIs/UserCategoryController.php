<?php

namespace App\Http\Controllers\Places\CategoryAPIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Location;

class UserCategoryController extends Controller
{
    public function fetchByUserCategories(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

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

        $limit = $request->query('limit', 20);

        $places = \App\Models\Location::with(['locationImages' => function ($query) {
            $query->where('status', 'verified');
        }, 'category'])
            ->whereIn('category_id', $categoryIds)
            ->limit($limit)
            ->get();

        return response()->json([
            'message' => 'Places fetched based on user categories',
            'count' => $places->count(),
            'data' => $places,
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
