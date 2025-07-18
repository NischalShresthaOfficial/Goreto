<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|exists:subscriptions,id',
            'payment_method_types' => 'required|array',
        ]);

        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $newSubscription = Subscription::findOrFail($request->subscription_id);
        $newSubId = $newSubscription->id;

        $existingSameSub = Payment::where('user_id', $user->id)
            ->where('subscription_id', $newSubId)
            ->where('subscription_status', 'active')
            ->first();

        if ($existingSameSub) {
            return response()->json([
                'error' => 'You already have an active subscription of this type.',
            ], 409);
        }

        $paidAt = now();
        $expiresAt = $paidAt->copy()->addDays($newSubscription->duration_days);

        $otherActive = Payment::where('user_id', $user->id)
            ->where('subscription_status', 'active')
            ->where('subscription_id', '!=', $newSubId)
            ->first();

        if ($otherActive) {
            $expiresAt = $otherActive->expires_at->copy()->addDays($newSubscription->duration_days);
            $otherActive->update(['subscription_status' => 'expired']);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($newSubscription->price * 100),
                'currency' => 'npr',
                'payment_method_types' => $request->payment_method_types,
                'metadata' => [
                    'user_id' => $user->id,
                    'subscription_id' => $newSubId,
                ],
            ]);

            $payment = Payment::create([
                'user_id' => $user->id,
                'stripe_payment_id' => $paymentIntent->id,
                'amount' => $newSubscription->price,
                'currency' => 'NPR',
                'status' => $paymentIntent->status,
                'subscription_status' => $expiresAt->isFuture() ? 'active' : 'expired',
                'payment_method' => $paymentIntent->payment_method ?? null,
                'subscription_id' => $newSubId,
                'paid_at' => $paidAt,
                'expires_at' => $expiresAt,
            ]);

            if ($paymentIntent->status === 'succeeded') {
                return $this->handleSuccessfulPayment($payment->id);
            }

            return response()->json([
                'message' => 'Payment intent created',
                'user_id' => $user->id,
                'client_secret' => $paymentIntent->client_secret,
                'payment_id' => $payment->id,
                'status' => $paymentIntent->status,
                'subscription_status' => $payment->subscription_status,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create payment intent',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function handleSuccessfulPayment(Request $request, $paymentId)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $userId = $request->input('user_id');

        $payment = Payment::where('id', $paymentId)
            ->where('user_id', $userId)
            ->first();

        if (! $payment) {
            return response()->json(['error' => 'Payment not found for this user'], 404);
        }

        Stripe::setApiKey(config('services.stripe.secret'));
        $stripePaymentIntent = PaymentIntent::retrieve($payment->stripe_payment_id);

        if ($stripePaymentIntent->status !== 'succeeded') {
            return response()->json(['error' => 'Payment is not successful yet'], 400);
        }

        if ($payment->status !== 'succeeded') {
            $payment->update([
                'status' => 'succeeded',
                'payment_method' => $stripePaymentIntent->payment_method,
            ]);
        }

        $pdfPath = 'invoices/invoice-'.$payment->id.'.pdf';
        if (! Storage::disk('public')->exists($pdfPath)) {
            $payment->load(['user', 'subscription']);
            $pdf = Pdf::loadView('invoice.invoice', compact('payment'));
            Storage::disk('public')->put($pdfPath, $pdf->output());
        }

        $invoiceUrl = Storage::disk('public')->url($pdfPath);

        return response()->json([
            'message' => 'Payment successful and invoice ready',
            'payment_id' => $payment->id,
            'status' => $payment->status,
            'invoice_url' => $invoiceUrl,
        ]);
    }
}
