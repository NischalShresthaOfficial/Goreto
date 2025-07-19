<?php

namespace App\Http\Controllers\Categories;

use App\Http\Controllers\Controller;
use App\Models\Category;

class FetchCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::paginate(10);

        return response()->json([
            'message' => 'Categories fetched successfully',
            'data' => $categories,
        ]);
    }
}
