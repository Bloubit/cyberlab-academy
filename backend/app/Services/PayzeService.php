<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayzeService
{
    private string $apiKey;
    private string $apiSecret;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.payze.api_key', '');
        $this->apiSecret = config('services.payze.api_secret', '');
        $this->baseUrl = config('services.payze.base_url', 'https://api.payze.io/v1');
    }

    /**
     * Create a payment and get checkout URL.
     */
    public function createPayment(Payment $payment, User $user): ?array
    {
        try {
            $amount = number_format($payment->amount, 2, '.', '');
            $hookUrl = config('app.url') . '/api/payments/payze/hook';
            $successUrl = config('app.url') . '/api/payments/payze/success?payment_id=' . $payment->id;
            $errorUrl = config('app.url') . '/api/payments/payze/error?payment_id=' . $payment->id;

            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->post("{$this->baseUrl}/merchant/payment", [
                    'amount' => $amount,
                    'currency' => $payment->currency,
                    'paymentMethod' => 'card',
                    'preauthorize' => false,
                    'hookUrl' => $hookUrl,
                    'successUrl' => $successUrl,
                    'errorUrl' => $errorUrl,
                    'metadata' => [
                        'payment_id' => $payment->id,
                        'user_id' => $user->id,
                        'description' => $payment->description ?? 'Subscription payment',
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $payment->update([
                    'stripe_payment_id' => $data['id'] ?? null,
                ]);

                return [
                    'payment_id' => $data['id'] ?? null,
                    'checkout_url' => $data['checkoutUrl'] ?? $data['url'] ?? null,
                    'transaction_id' => $data['transactionId'] ?? null,
                ];
            }

            Log::error('Payze create payment failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Exception $e) {
            Log::error('Payze create payment error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Handle Payze webhook (payment status update).
     */
    public function handleWebhook(array $payload): bool
    {
        try {
            // Verify webhook signature if provided
            $signature = $payload['signature'] ?? $payload['sign'] ?? null;
            if ($signature) {
                $payloadCopy = $payload;
                unset($payloadCopy['signature'], $payloadCopy['sign']);
                $expectedSign = hash_hmac('sha256', json_encode($payloadCopy), $this->apiSecret);
                if ($signature !== $expectedSign) {
                    Log::warning('Payze webhook: invalid signature');
                    return false;
                }
            }

            $transactionId = $payload['transactionId'] ?? $payload['id'] ?? null;
            $status = $payload['status'] ?? $payload['paymentStatus'] ?? null;
            $metadata = $payload['metadata'] ?? [];
            $paymentId = $metadata['payment_id'] ?? $payload['payment_id'] ?? null;

            if (!$paymentId) {
                Log::warning('Payze webhook: no payment_id in metadata');
                return false;
            }

            $payment = Payment::find($paymentId);
            if (!$payment) {
                Log::warning('Payze webhook: payment not found', ['payment_id' => $paymentId]);
                return false;
            }

            switch ($status) {
                case 'Captured':
                case 'Success':
                case 'success':
                    $payment->complete($transactionId);
                    $payment->update(['payment_method' => 'payze']);

                    if ($payment->subscription_id) {
                        $subscription = $payment->subscription;
                        if ($subscription) {
                            $subscription->update([
                                'status' => 'active',
                                'current_period_start' => now(),
                                'current_period_end' => now()->addMonth(),
                            ]);
                        }
                    }
                    break;

                case 'Failed':
                case 'failed':
                    $payment->update(['status' => 'failed']);
                    break;

                case 'Refunded':
                case 'refunded':
                    $payment->refund('Refunded via Payze');
                    break;

                default:
                    Log::info('Payze webhook: unknown status', ['status' => $status]);
                    break;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Payze webhook error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Refund a payment via Payze.
     */
    public function refundPayment(Payment $payment): bool
    {
        try {
            $transactionId = $payment->stripe_payment_id;

            if (!$transactionId) {
                return false;
            }

            $amount = number_format($payment->amount, 2, '.', '');

            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->post("{$this->baseUrl}/merchant/refund", [
                    'transactionId' => $transactionId,
                    'amount' => $amount,
                    'reason' => 'Refund requested',
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Payze refund error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get payment status from Payze.
     */
    public function getPaymentStatus(string $transactionId): ?array
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
                ->get("{$this->baseUrl}/merchant/payment/{$transactionId}");

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Payze status check error: ' . $e->getMessage());
        }

        return null;
    }
}
