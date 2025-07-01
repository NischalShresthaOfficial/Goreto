<?php

namespace App\Http\Controllers\Places\CategoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class CategoryPlacesController extends Controller
{
    public function fetchByCategory(Request $request)
    {
        $request->validate([
            'category' => ['required', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $categoryName = $request->query('category');
        $limit = $request->query('limit', 50);

        $locations = Location::with(['verifiedImages', 'city', 'category'])
            ->whereHas('category', function ($query) use ($categoryName) {
                $query->where('category', 'LIKE', "%{$categoryName}%");
            })
            ->limit($limit)
            ->get();

        $locations->transform(function ($location) {
            $location->locationImages = $location->verifiedImages->isNotEmpty()
                ? $location->verifiedImages
                : null;

            unset($location->verifiedImages);

            return $location;
        });

        return response()->json([
            'message' => 'Places fetched by category',
            'count' => $locations->count(),
            'data' => $locations,
        ]);
    }
}
