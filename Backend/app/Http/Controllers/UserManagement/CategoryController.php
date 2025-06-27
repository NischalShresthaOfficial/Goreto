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

        UserCategory::where('user_id', $user->id)->delete();

        $categories = Category::whereIn('category', $validated['categories'])->get();

        foreach ($categories as $category) {
            UserCategory::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
            ]);
        }

        return response()->json([
            'message' => 'Categories assigned successfully',
            'categories' => $categories,
        ]);
    }
}
