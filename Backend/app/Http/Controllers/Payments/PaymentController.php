<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'currency' => 'required|string|in:USD,NPR',
            'payment_method_types' => 'required|array',
        ]);

        $user = Auth::user();

        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $subscription = Subscription::findOrFail($request->subscription_id);
        $amount = $subscription->price;
        $durationDays = $subscription->duration_days;

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => strtolower($request->currency),
                'payment_method_types' => $request->payment_method_types ?? ['card'],
                'metadata' => [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                ],
            ]);

            $paidAt = now();
            $durationDays = (int) $subscription->duration_days;
            $expiresAt = $paidAt->copy()->addDays($durationDays);

            $payment = Payment::create([
                'user_id' => $user->id,
                'stripe_payment_id' => $paymentIntent->id,
                'amount' => $amount,
                'currency' => strtoupper($request->currency),
                'status' => $paymentIntent->status,
                'payment_method' => null,
                'subscription_id' => $subscription->id,
                'paid_at' => $paidAt,
                'expires_at' => $expiresAt,
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
