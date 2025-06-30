<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FetchPostController extends Controller
{
    public function myPosts()
    {
        $posts = Post::with(['user', 'contents', 'locations'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json($posts);
    }

    public function userPosts($userId)
    {
        $posts = Post::with(['user', 'contents', 'locations'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json($posts);
    }

    public function feed()
    {
        $posts = Post::with(['user', 'contents', 'locations'])
            ->latest()
            ->get();

        return response()->json($posts);
    }
}
