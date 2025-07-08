<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\ProfilePicture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfilePictureController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        $oldPicture = ProfilePicture::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($oldPicture) {
            $oldPicture->is_active = false;
            $oldPicture->save();

            Storage::disk('public')->delete($oldPicture->profile_picture_url);
        }

        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        $newPicture = ProfilePicture::create([
            'user_id' => $user->id,
            'profile_picture_url' => $path,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Profile picture uploaded successfully.',
            'profile_picture' => asset('storage/'.$newPicture->profile_picture_url),
        ], 201);
    }

    public function fetch(Request $request)
    {
        $user = $request->user();

        $picture = $user->profilePicture()
            ->where('is_active', true)
            ->latest()
            ->first();

        if (! $picture) {
            return response()->json(['message' => 'No active profile picture found'], 404);
        }

        return response()->json([
            'message' => 'Active profile picture fetched successfully',
            'profile_picture' => [
                'url' => asset('storage/'.$picture->profile_picture_url),
                'uploaded_at' => $picture->created_at,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();

        $oldPicture = ProfilePicture::where('user_id', $user->id)
            ->where('is_active', true)
            ->first();

        if ($oldPicture) {
            $oldPicture->is_active = false;
            $oldPicture->save();

            Storage::disk('public')->delete($oldPicture->profile_picture_url);
        }

        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        $newPicture = ProfilePicture::create([
            'user_id' => $user->id,
            'profile_picture_url' => $path,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Profile picture updated successfully.',
            'profile_picture' => asset('storage/'.$newPicture->profile_picture_url),
        ]);
    }
}
