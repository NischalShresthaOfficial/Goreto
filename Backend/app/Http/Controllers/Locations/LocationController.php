<?php

namespace App\Http\Controllers\Locations;

use App\Http\Controllers\Controller;
use App\Models\Location;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::with(['category', 'city'])->paginate(10);

        return response()->json([
            'message' => 'Locations fetched successfully',
            'data' => $locations,
        ]);
    }
}
