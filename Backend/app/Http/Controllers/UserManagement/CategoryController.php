<?php

namespace App\Http\Controllers\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\UserCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'categories' => ['required', 'array'],
            'categories.*' => ['string', 'exists:categories,category'],
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'No authenticated user found'], 401);
        }

        $existingCategoryIds = UserCategory::where('user_id', $user->id)
            ->pluck('category_id')
            ->toArray();

        $categoriesToAdd = Category::whereIn('category', $validated['categories'])->get();

        $addedCategories = [];

        foreach ($categoriesToAdd as $category) {
            if (! in_array($category->id, $existingCategoryIds)) {
                UserCategory::create([
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                ]);
                $addedCategories[] = $category;
            }
        }

        return response()->json([
            'message' => 'Categories added successfully',
            'added_categories' => $addedCategories,
        ]);
    }
}
