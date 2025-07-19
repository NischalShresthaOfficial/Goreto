<?php

namespace App\Http\Controllers\Categories;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class FetchCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->has('search') && $request->search !== null) {
            $query->where('category', 'like', '%'.$request->search.'%');
        }

        $categories = $query->paginate(10);

        return response()->json([
            'message' => 'Categories fetched successfully',
            'data' => $categories,
        ]);
    }
}
