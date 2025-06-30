<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostContent;
use App\Models\PostLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CreatePostController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'nullable|string',
            'location_id' => 'nullable|exists:locations,id',
            'media' => 'required|array',
            'media.*' => 'required|file|mimes:jpeg,png,jpg,mp4|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $post = Post::create([
            'user_id' => Auth::id(),
            'caption' => $request->caption,
        ]);


        foreach ($request->file('media') as $file) {
            $path = $file->store('posts', 'public');
            PostContent::create([
                'post_id' => $post->id,
                'media_path' => $path,
                'media_type' => $file->getClientMimeType(),
            ]);
        }

    
        if ($request->location_id) {
            PostLocation::create([
                'post_id' => $post->id,
                'location_id' => $request->location_id,
            ]);
        }

        return response()->json(['message' => 'Post created successfully', 'post' => $post], 201);
    }
}
