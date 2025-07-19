<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostContent;
use App\Models\PostLike;
use App\Models\PostLocation;
use App\Models\PostNotification;
use App\Models\PostReport;
use App\Models\PostReview;
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

            $interestedUserIds = \App\Models\UserCategory::whereIn('category_id', $request->category_ids)
                ->where('user_id', '!=', Auth::id())
                ->pluck('user_id')
                ->unique();

            foreach ($interestedUserIds as $userId) {
                PostNotification::create([
                    'title' => 'New Post in Your Interest',
                    'content' => substr($request->description, 0, 100),
                    'user_id' => $userId,
                    'post_id' => $post->id,
                ]);
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

        $locationId = $request->query('location_id');

        $postsQuery = Post::whereHas('postCategory', function ($query) use ($userCategoryIds) {
            $query->whereIn('category_id', $userCategoryIds);
        })
            ->where('user_id', '!=', $user->id);

        if ($locationId) {
            $postsQuery->whereHas('postLocations', function ($query) use ($locationId) {
                $query->where('location_id', $locationId);
            });
        }

        $posts = $postsQuery->with([
            'postCategory.category',
            'postContents',
            'postLocations.location',
            'user.profilePicture' => function ($query) {
                $query->where('is_active', true)->select('id', 'user_id', 'profile_picture_url');
            },
        ])
            ->paginate(10);

        $posts->getCollection()->transform(function ($post) {
            $profilePicture = $post->user->profilePicture->first() ?? null;
            $post->user_info = [
                'id' => $post->user->id,
                'name' => $post->user->name,
                'profile_picture_url' => $profilePicture
                    ? Storage::disk('public')->url($profilePicture->profile_picture_url)
                    : null,
            ];
            unset($post->user);

            return $post;
        });

        return response()->json($posts);
    }

    public function fetchById($postId)
    {
        $post = Post::with([
            'postCategory.category',
            'postContents',
            'postLocations.location',
            'user.profilePicture' => function ($query) {
                $query->where('is_active', true)->select('id', 'user_id', 'profile_picture_url');
            }])
            ->find($postId);

        if (! $post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $profilePicture = $post->user->profilePicture->first() ?? null;

        $post->user_info = [
            'id' => $post->user->id,
            'name' => $post->user->name,
            'profile_picture_url' => $profilePicture
                ? Storage::disk('public')->url($profilePicture->profile_picture_url)
                : null,
        ];

        unset($post->user);

        return response()->json($post);
    }

    public function fetchMyPosts(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'No authenticated user found'], 401);
        }

        $posts = Post::where('user_id', $user->id)
            ->with([
                'postCategory.category',
                'postContents',
                'postLocations.location',
                'user.profilePicture' => function ($query) {
                    $query->where('is_active', true)->select('id', 'user_id', 'profile_picture_url');
                },
            ])
            ->paginate(10);

        $posts->getCollection()->transform(function ($post) {
            $profilePicture = $post->user->profilePicture->first() ?? null;
            $post->user_info = [
                'id' => $post->user->id,
                'name' => $post->user->name,
                'profile_picture_url' => $profilePicture
                    ? Storage::disk('public')->url($profilePicture->profile_picture_url)
                    : null,
            ];
            unset($post->user);

            return $post;
        });

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

    public function storeReview(Request $request, $postId)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'No authenticated user found'], 401);
        }

        $request->validate([
            'review' => 'required|string|max:1000',
        ]);

        $post = Post::find($postId);

        if (! $post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $review = PostReview::create([
            'review' => $request->review,
            'user_id' => $user->id,
            'post_id' => $postId,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully.',
            'review' => $review,
        ], 201);
    }

    public function editReview(Request $request, $postId, $reviewId)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'No authenticated user found'], 401);
        }

        $request->validate([
            'review' => 'required|string|max:1000',
        ]);

        $review = PostReview::where('id', $reviewId)
            ->where('post_id', $postId)
            ->where('user_id', $user->id)
            ->first();

        if (! $review) {
            return response()->json(['error' => 'Review not found or access denied'], 404);
        }

        $review->review = $request->review;
        $review->save();

        return response()->json([
            'message' => 'Review updated successfully.',
            'review' => $review,
        ]);
    }

    public function fetchReviews($postId)
    {
        $post = Post::find($postId);

        if (! $post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $reviews = $post->postReviews()->with('user:id,name,email')->paginate(10);

        return response()->json($reviews);
    }

    public function fetchReviewById($postId, $reviewId)
    {
        $review = PostReview::where('post_id', $postId)
            ->where('id', $reviewId)
            ->with('user:id,name,email')
            ->first();

        if (! $review) {
            return response()->json(['error' => 'Review not found'], 404);
        }

        return response()->json($review);
    }

    public function report(Request $request, $postId)
    {
        $request->validate([
            'offense_type' => 'required|in:spam,harassment,hate_speech,nudity,violence',
        ]);

        $user = Auth::user();

        $post = Post::find($postId);
        if (! $post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $alreadyReported = PostReport::where('post_id', $postId)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyReported) {
            return response()->json(['message' => 'You have already reported this post.'], 400);
        }

        $report = PostReport::create([
            'user_id' => $user->id,
            'post_id' => $postId,
            'offense_type' => $request->offense_type,
        ]);

        return response()->json([
            'message' => 'Post reported successfully.',
            'report' => $report,
        ], 201);
    }

    public function likePost(Request $request, $postId)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'No authenticated user found'], 401);
        }

        $post = Post::find($postId);

        if (! $post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $alreadyLiked = PostLike::where('post_id', $postId)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyLiked) {
            return response()->json(['message' => 'You have already liked this post.'], 400);
        }

        DB::beginTransaction();

        try {
            PostLike::create([
                'post_id' => $postId,
                'user_id' => $user->id,
            ]);

            $post->increment('likes');

            DB::commit();

            return response()->json([
                'message' => 'Post liked successfully.',
                'likes' => $post->likes,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to like post.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchLikes($postId)
    {
        $post = Post::find($postId);

        if (! $post) {
            return response()->json(['error' => 'Post not found'], 404);
        }

        $likes = PostLike::where('post_id', $postId)
            ->with(['user' => function ($query) {
                $query->select('id', 'name')->with(['profilePicture' => function ($q) {
                    $q->where('is_active', true)->select('id', 'user_id', 'profile_picture_url');
                }]);
            }])
            ->get()
            ->map(function ($like) {
                $user = $like->user;
                $profilePicture = $user->profilePicture->first();

                $profilePictureUrl = $profilePicture
                    ? Storage::disk('public')->url($profilePicture->profile_picture_url)
                    : null;

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'profile_picture_url' => $profilePictureUrl,
                ];
            });

        return response()->json([
            'post_id' => $postId,
            'total_likes' => $likes->count(),
            'liked_by' => $likes,
        ]);
    }
}