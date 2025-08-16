<?php

namespace App\Http\Controllers\Posts;

use App\Http\Controllers\Controller;
use App\Models\PostNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostNotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $notifications = PostNotification::where('user_id', $user->id)
            ->with('post:id,description')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($notifications);
    }
}
