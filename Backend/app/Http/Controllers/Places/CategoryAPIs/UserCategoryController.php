<?php

namespace App\Http\Controllers\Places\CategoryAPIs;

use App\Http\Controllers\Controller;
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

        $places = \App\Models\Location::with('locationImages', 'category')
            ->whereIn('category_id', $categoryIds)
            ->limit($limit)
            ->get();

        return response()->json([
            'message' => 'Places fetched based on user categories',
            'count' => $places->count(),
            'data' => $places,
        ]);
    }
}
