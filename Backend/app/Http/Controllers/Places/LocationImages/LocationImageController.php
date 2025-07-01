<?php

namespace App\Http\Controllers\Places\LocationImages;

use App\Http\Controllers\Controller;
use App\Mail\LocationImageUploadedMail;
use App\Models\EmailNotification;
use App\Models\Location;
use App\Models\LocationImage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LocationImageController extends Controller
{
    public function store(Request $request, $locationId)
    {
        $location = Location::findOrFail($locationId);

        $request->validate([
            'image' => 'required|image|max:5120',
        ]);

        $file = $request->file('image');
        $path = $file->storeAs(
            'locations',
            Str::uuid().'.'.$file->getClientOriginalExtension(),
            'public'
        );

        $image = LocationImage::create([
            'location_id' => $location->id,
            'image_path' => $path,
            'status' => 'unverified',
        ]);

        $user = $request->user();

        $systemEmail = config('mail.from.address');
        $systemUser = User::where('email', $systemEmail)->first();

        Mail::to($systemEmail)->queue(new LocationImageUploadedMail($image, $user));

        EmailNotification::create([
            'title' => 'New Location Image Uploaded',
            'description' => "{$user->name} uploaded an image for location '{$location->name}'",
            'sender_id' => $user->id,
            'receiver_id' => $systemUser?->id,
        ]);

        return response()->json([
            'message' => 'Image uploaded successfully.',
            'image' => $image,
        ], 201);
    }
}
