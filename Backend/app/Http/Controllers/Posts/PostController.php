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
use Illuminate\Support\Facades\Storage;

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

    public function editPost(Request $request, $postId)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'No authenticated user found'], 401);
        }

        $request->validate([
            'description' => 'sometimes|required|string',
            'location_ids' => 'sometimes|required|array',
            'location_ids.*' => 'exists:locations,id',
            'category_ids' => 'sometimes|required|array',
            'category_ids.*' => 'exists:categories,id',
            'contents' => 'nullable|array',
            'contents.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:20480',
        ]);

        $post = Post::where('id', $postId)->where('user_id', $user->id)->first();

        if (! $post) {
            return response()->json(['error' => 'Post not found or access denied'], 404);
        }

        DB::beginTransaction();

        try {
            if ($request->has('description')) {
                $post->description = $request->description;
            }

            if ($request->has('status')) {
                $post->status = $request->input('status', 'active');
            }

            $post->save();

            if ($request->has('location_ids')) {
                PostLocation::where('post_id', $post->id)->delete();
                foreach ($request->location_ids as $locationId) {
                    PostLocation::create([
                        'post_id' => $post->id,
                        'location_id' => $locationId,
                    ]);
                }
            }

            if ($request->has('category_ids')) {
                PostCategory::where('post_id', $post->id)->delete();
                foreach ($request->category_ids as $categoryId) {
                    PostCategory::create([
                        'post_id' => $post->id,
                        'category_id' => $categoryId,
                    ]);
                }
            }

            if ($request->hasFile('contents')) {
                foreach ($post->postContents as $oldContent) {
                    Storage::disk('public')->delete($oldContent->content_path);
                }

                PostContent::where('post_id', $post->id)->delete();

                foreach ($request->file('contents') as $file) {
                    $path = $file->store('post_contents', 'public');

                    PostContent::create([
                        'post_id' => $post->id,
                        'content_path' => $path,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Post updated successfully.',
                'post_id' => $post->id,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Post update failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function deletePost(Request $request, $postId)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'No authenticated user found'], 401);
        }

        $post = Post::where('id', $postId)->where('user_id', $user->id)->first();

        if (! $post) {
            return response()->json(['error' => 'Post not found or access denied'], 404);
        }

        DB::beginTransaction();

        try {
            foreach ($post->postContents as $content) {
                Storage::disk('public')->delete($content->content_path);
            }

            $post->delete();

            DB::commit();

            return response()->json([
                'message' => 'Post deleted successfully.',
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to delete post.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
