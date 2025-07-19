<?php

namespace App\Http\Controllers\Locations;

use App\Http\Controllers\Controller;
use App\Models\Location;

class LocationController extends Controller
{
    public function index()
    {
        $query = Location::with(['category', 'city']);

        if (request()->has('search') && request()->search !== null) {
            $searchTerm = request()->search;
            $query->where('name', 'like', '%'.$searchTerm.'%');
        }

        $locations = $query->paginate(10);

        return response()->json([
            'message' => 'Locations fetched successfully',
            'data' => $locations,
        ]);
    }
}
