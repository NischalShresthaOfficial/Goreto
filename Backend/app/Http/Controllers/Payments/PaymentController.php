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

            $payment->load(['user', 'subscription']);

            $pdf = Pdf::loadView('invoice.invoice', compact('payment'));

            $pdfPath = 'invoices/invoice-'.$payment->id.'.pdf';
            Storage::disk('public')->put($pdfPath, $pdf->output());

            $invoiceUrl = Storage::disk('public')->url($pdfPath);

            return response()->json([
                'message' => 'Payment intent created',
                'client_secret' => $paymentIntent->client_secret,
                'payment_id' => $payment->id,
                'status' => $paymentIntent->status,
                'subscription_status' => $payment->subscription_status,
                'invoice_url' => $invoiceUrl,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create payment intent',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
