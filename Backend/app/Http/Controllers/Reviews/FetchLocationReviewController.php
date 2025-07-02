<?php

namespace App\Http\Controllers\Reviews;

use App\Http\Controllers\Controller;
use App\Models\LocationReview;
use Illuminate\Http\Request;

class FetchLocationReviewController extends Controller
{
    public function fetchByLocationId(Request $request, $locationId)
    {
        if (! is_numeric($locationId)) {
            return response()->json(['message' => 'Invalid location ID'], 400);
        }

        $limit = $request->query('limit', 10);

        $reviews = LocationReview::with('user')
            ->where('location_id', $locationId)
            ->orderBy('created_at', 'desc')
            ->paginate($limit);

        return response()->json([
            'message' => 'Reviews fetched successfully',
            'total' => $reviews->total(),
            'per_page' => $reviews->perPage(),
            'current_page' => $reviews->currentPage(),
            'last_page' => $reviews->lastPage(),
            'count' => $reviews->count(),
            'data' => $reviews->items(),
        ]);
    }
}
