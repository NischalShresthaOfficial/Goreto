<?php

namespace App\Http\Controllers\Subscriptions;

use App\Http\Controllers\Controller;
use App\Models\Subscription;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = Subscription::paginate(10);

        return response()->json([
            'message' => 'Subscriptions fetched successfully',
            'data' => $subscriptions,
        ]);
    }
}
