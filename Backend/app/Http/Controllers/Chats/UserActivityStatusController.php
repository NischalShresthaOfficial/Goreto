<?php

namespace App\Http\Controllers\Chats;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserActivityStatusController extends Controller
{
    public function updateActivityStatus(Request $request)
    {
        $request->validate([
            'activity_status' => ['required', 'boolean'],
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user->activity_status = $request->input('activity_status');
        $user->save();

        return response()->json([
            'message' => 'Activity status updated successfully',
            'activity_status' => $user->activity_status,
        ]);
    }
}
