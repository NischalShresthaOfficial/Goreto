<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostContent;
use App\Models\PostLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string',
            'location_ids' => 'required|array',
            'location_ids.*' => 'exists:locations,id',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
            'contents' => 'required|array',
            'contents.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:20480',
        ]);

        DB::beginTransaction();

        try {
            $post = Post::create([
                'description' => $request->description,
                'status' => $request->input('status', 'active'),
                'likes' => 0,
                'user_id' => Auth::id(),
            ]);

            foreach ($request->location_ids as $locationId) {
                PostLocation::create([
                    'post_id' => $post->id,
                    'location_id' => $locationId,
                ]);
            }

            foreach ($request->category_ids as $categoryId) {
                PostCategory::create([
                    'post_id' => $post->id,
                    'category_id' => $categoryId,
                ]);
            }

            if ($request->hasFile('contents')) {
                foreach ($request->file('contents') as $file) {
                    $path = $file->store('post_contents', 'public');

                    $created = PostContent::create([
                        'post_id' => $post->id,
                        'content_path' => $path,
                    ]);

                    logger('Created PostContent: '.json_encode($created));
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Post created successfully.',
                'post_id' => $post->id,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Post creation failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetch(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'No authenticated user found'], 401);
        }

        $userCategoryIds = $user->userCategories()->pluck('category_id');

        $posts = Post::whereHas('postCategory', function ($query) use ($userCategoryIds) {
            $query->whereIn('category_id', $userCategoryIds);
        })
            ->with(['postCategory.category', 'postContents', 'postLocations.location'])
            ->paginate(10);

        return response()->json($posts);
    }

    public function fetchMyPosts(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'No authenticated user found'], 401);
        }

        $posts = Post::where('user_id', $user->id)
            ->with(['postCategory.category', 'postContents', 'postLocations.location'])
            ->paginate(10);

        return response()->json($posts);
    }
}