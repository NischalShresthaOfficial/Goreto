<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class FetchPostController extends Controller
{
    public function myPosts()
    {
        return response()->json(
            Post::with(['postContents', 'postLocations', 'user'])
                ->where('user_id', Auth::id())
                ->latest()
                ->get()
        );
    }

    public function userPosts($userId)
    {
        return response()->json(
            Post::with(['postContents', 'postLocations', 'user'])
                ->where('user_id', $userId)
                ->latest()
                ->get()
        );
    }

    public function feed()
    {
        return response()->json(
            Post::with(['postContents', 'postLocations', 'user'])
                ->latest()
                ->get()
        );
    }
}
