<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostBookmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostBookmarkController extends Controller
{
    public function store(Request $request, $postId)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $post = Post::find($postId);

        if (! $post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $alreadyBookmarked = PostBookmark::where('user_id', $user->id)
            ->where('post_id', $postId)
            ->exists();

        if ($alreadyBookmarked) {
            return response()->json(['message' => 'Post already bookmarked'], 200);
        }

        $bookmark = PostBookmark::create([
            'user_id' => $user->id,
            'post_id' => $postId,
        ]);

        return response()->json([
            'message' => 'Post bookmarked successfully',
            'bookmark' => $bookmark,
        ], 201);
    }

    public function fetchBookmarks(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $bookmarkedPosts = $user->postBookmarks()
            ->with(['post.postCategory.category', 'post.postContents', 'post.postLocations.location'])
            ->paginate(10);

        return response()->json($bookmarkedPosts);
    }

    public function fetchById(Request $request, $bookmarkId)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $bookmark = \App\Models\PostBookmark::with([
            'post.postCategory.category',
            'post.postContents',
            'post.postLocations.location',
        ])
            ->where('user_id', $user->id)
            ->where('id', $bookmarkId)
            ->first();

        if (! $bookmark) {
            return response()->json(['error' => 'Bookmark not found'], 404);
        }

        return response()->json($bookmark);
    }
}
