<?php

namespace App\Http\Controllers\Reviews;

use App\Http\Controllers\Controller;
use App\Models\LocationReview;
use Illuminate\Http\Request;

class FetchLocationReviewController extends Controller
{
    public function fetchByLocationId(Request $request, $locationId)
    {
        if (!is_numeric($locationId)) {
            return response()->json(['message' => 'Invalid location ID'], 400);
        }

        $reviews = LocationReview::with('user')
            ->where('location_id', $locationId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Reviews fetched successfully',
            'count' => $reviews->count(),
            'data' => $reviews,
        ]);
    }
}
