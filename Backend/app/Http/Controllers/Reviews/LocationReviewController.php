<?php

namespace App\Http\Controllers\Reviews;

use App\Http\Controllers\Controller;
use App\Models\LocationReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationReviewController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'review' => ['required', 'string'],
            'rating' => [
                'required',
                'numeric',
                'between:1,5',
                'regex:/^(?:[1-4](?:\.5)?|5)$/',
            ],
        ]);

        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $review = LocationReview::create([
            'user_id' => $user->id,
            'location_id' => $request->location_id,
            'review' => $request->review,
            'rating' => $request->rating,
        ]);

        return response()->json([
            'message' => 'Review created successfully',
            'data' => $review,
        ], 201);
    }
}
