<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount' => 'required|integer|min:50',
            'currency' => 'required|string|in:USD,NPR',
            'payment_method_types' => 'nullable|array',
        ]);

        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->amount,
                'currency' => strtolower($request->currency),
                'payment_method_types' => $request->payment_method_types ?? ['card'],
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);

            $payment = Payment::create([
                'user_id' => $user->id,
                'stripe_payment_id' => $paymentIntent->id,
                'amount' => $request->amount,
                'currency' => strtoupper($request->currency),
                'status' => $paymentIntent->status,
                'payment_method' => null,
            ]);

            return response()->json([
                'message' => 'Payment intent created',
                'client_secret' => $paymentIntent->client_secret,
                'payment_id' => $payment->id,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create payment intent',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
