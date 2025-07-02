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
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $categoryName = $request->query('category');
        $limit = $request->query('limit', 50);

        $query = Location::with(['verifiedImages', 'city', 'category'])
            ->whereHas('category', function ($query) use ($categoryName) {
                $query->where('category', 'LIKE', "%{$categoryName}%");
            });

        $locations = $query->paginate($limit);

        $transformed = $locations->getCollection()->transform(function ($location) {
            $location->locationImages = $location->verifiedImages->isNotEmpty()
                ? $location->verifiedImages
                : null;

            unset($location->verifiedImages);

            return $location;
        });

        $locations->setCollection($transformed);

        return response()->json([
            'message' => 'Places fetched by category',
            'total' => $locations->total(),
            'per_page' => $locations->perPage(),
            'current_page' => $locations->currentPage(),
            'last_page' => $locations->lastPage(),
            'count' => $locations->count(),
            'data' => $locations->items(),
        ]);
    }

    public function fetchById($id)
    {
        $location = Location::with(['verifiedImages', 'city', 'category'])->find($id);

        if (! $location) {
            return response()->json([
                'message' => 'Location not found',
            ], 404);
        }

        $location->locationImages = $location->verifiedImages->isNotEmpty()
            ? $location->verifiedImages
            : null;

        unset($location->verifiedImages);

        return response()->json([
            'message' => 'Place fetched successfully',
            'data' => $location,
        ]);
    }
}
