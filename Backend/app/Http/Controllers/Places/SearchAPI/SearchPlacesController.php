<?php

namespace App\Http\Controllers\Places\SearchAPI;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchPlacesController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'query' => ['required', 'string'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $searchQuery = $request->input('query');
        $limit = $request->input('limit', 10);

        SearchHistory::create([
            'user_id' => Auth::id(),
            'query' => $searchQuery,
        ]);

        $results = Location::with(['locationImages', 'category', 'city'])
            ->where('name', 'like', '%'.$searchQuery.'%')
            ->orWhereHas('category', fn ($q) => $q->where('category', 'like', '%'.$searchQuery.'%'))
            ->orWhereHas('city', fn ($q) => $q->where('city', 'like', '%'.$searchQuery.'%'))
            ->limit($limit)
            ->get();

        return response()->json([
            'message' => 'Search results from local database',
            'count' => $results->count(),
            'data' => $results,
        ]);
    }
}
