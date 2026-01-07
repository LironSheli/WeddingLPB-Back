<?php

namespace App\Http\Controllers;

use App\Models\WeddingPage;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function generatePaymentLink(Request $request, int $id)
    {
        $page = WeddingPage::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $request->validate([
            'type' => 'required|in:full,deposit,balance',
        ]);

        $amount = match($request->type) {
            'full' => 300.00,
            'deposit' => 100.00,
            'balance' => 200.00,
        };

        // Create payment record
        $payment = Payment::create([
            'wedding_page_id' => $page->id,
            'type' => $request->type,
            'amount' => $amount,
            'status' => 'pending',
        ]);

        // Generate Lemon Squeezy checkout URL
        $checkoutUrl = $this->createLemonSqueezyCheckout($page, $payment, $amount);

        return response()->json([
            'payment_url' => $checkoutUrl,
            'payment_id' => $payment->id,
        ]);
    }

    public function handleWebhook(Request $request)
    {
        $signature = $request->header('X-Signature');
        $payload = $request->getContent();

        // Verify webhook signature
        $expectedSignature = hash_hmac('sha256', $payload, config('services.lemon_squeezy.webhook_secret'));
        
        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data = $request->json()->all();
        $event = $data['meta']['event_name'] ?? null;

        if ($event === 'order_created' || $event === 'subscription_payment_success') {
            $orderId = $data['data']['id'] ?? null;
            $payment = Payment::where('provider_reference', $orderId)->first();

            if ($payment) {
                $payment->update([
                    'status' => 'completed',
                    'provider_data' => $data,
                ]);

                $page = $payment->weddingPage;
                if ($payment->type === 'full') {
                    $page->update(['status' => 'live']);
                    // TODO: Send payment confirmation email
                }
            }
        }

        return response()->json(['success' => true]);
    }

    private function createLemonSqueezyCheckout(WeddingPage $page, Payment $payment, float $amount): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.lemon_squeezy.api_key'),
            'Accept' => 'application/vnd.api+json',
            'Content-Type' => 'application/vnd.api+json',
        ])->post('https://api.lemonsqueezy.com/v1/checkouts', [
            'data' => [
                'type' => 'checkouts',
                'attributes' => [
                    'custom_price' => $amount * 100, // Amount in cents
                    'product_options' => [
                        'name' => 'Wedding Landing Page',
                        'description' => "Wedding page for {$page->slug}",
                    ],
                    'checkout_options' => [
                        'embed' => false,
                        'media' => false,
                    ],
                    'checkout_data' => [
                        'custom' => [
                            'payment_id' => $payment->id,
                            'page_id' => $page->id,
                        ],
                    ],
                    'expires_at' => now()->addDays(7)->toIso8601String(),
                    'preview' => false,
                ],
                'relationships' => [
                    'store' => [
                        'data' => [
                            'type' => 'stores',
                            'id' => config('services.lemon_squeezy.store_id'),
                        ],
                    ],
                ],
            ],
        ]);

        $checkoutData = $response->json();
        $checkoutUrl = $checkoutData['data']['attributes']['url'] ?? '';

        $payment->update([
            'provider_reference' => $checkoutData['data']['id'] ?? null,
        ]);

        return $checkoutUrl;
    }
}

