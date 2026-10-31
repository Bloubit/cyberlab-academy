<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickService
{
    private string $merchantId;
    private string $serviceId;
    private string $secretKey;
    private string $merchantUserId;
    private string $baseUrl;

    public function __construct()
    {
        $this->merchantId = config('services.click.merchant_id', '');
        $this->serviceId = config('services.click.service_id', '');
        $this->secretKey = config('services.click.secret_key', '');
        $this->merchantUserId = config('services.click.merchant_user_id', '');
        $this->baseUrl = config('services.click.base_url', 'https://api.click.uz/v2/merchant');
    }

    /**
     * Generate Click payment URL for a subscription.
     */
    public function generatePaymentUrl(Payment $payment, User $user): string
    {
        $amount = $payment->amount * 100; // Convert to tiyin (Click uses smallest unit)
        $transactionParam = $payment->id;

        $params = [
            'merchant_id' => $this->merchantId,
            'service_id' => $this->serviceId,
            'amount' => $amount,
            'transaction_param' => $transactionParam,
            'return_url' => config('app.url') . '/api/payments/click/return',
            'callback_url' => config('app.url') . '/api/payments/click/callback',
        ];

        $signString = sprintf(
            '%s%s%s%s',
            $this->merchantId,
            $transactionParam,
            $amount,
            $this->secretKey
        );
        $params['sign_time'] = now()->timestamp;
        $params['sign_string'] = md5($signString);

        $query = http_build_query($params);
        return "https://my.click.uz/services/pay?{$query}";
    }

    /**
     * Handle Click Prepare request (validate order before payment).
     */
    public function handlePrepare(array $request): array
    {
        try {
            $clickTransId = $request['click_trans_id'] ?? '';
            $serviceId = $request['service_id'] ?? '';
            $clickPaydocId = $request['click_paydoc_id'] ?? '';
            $merchantTransId = $request['merchant_trans_id'] ?? '';
            $amount = $request['amount'] ?? 0;
            $action = $request['action'] ?? 0;
            $signTime = $request['sign_time'] ?? '';
            $signString = $request['sign_string'] ?? '';

            // Validate service_id
            if ((string)$serviceId !== (string)$this->serviceId) {
                return $this->error(-1, 'Service ID not found');
            }

            // Find payment
            $payment = Payment::find($merchantTransId);
            if (!$payment) {
                return $this->error(-5, 'Payment not found');
            }

            // Validate amount
            $expectedAmount = $payment->amount * 100;
            if ((int)$amount !== (int)$expectedAmount) {
                return $this->error(-2, 'Incorrect amount');
            }

            // Verify signature
            $expectedSign = md5(
                $clickTransId . $this->secretKey . $merchantTransId . $serviceId .
                $amount . $action . $signTime
            );

            if ($signString !== $expectedSign) {
                return $this->error(-1, 'Invalid signature');
            }

            // Check payment status
            if ($payment->status === 'completed') {
                return $this->error(-4, 'Already paid');
            }

            return $this->success($clickPaydocId, $merchantTransId);
        } catch (\Exception $e) {
            Log::error('Click Prepare error: ' . $e->getMessage());
            return $this->error(-99, 'Internal error');
        }
    }

    /**
     * Handle Click Complete request (confirm payment).
     */
    public function handleComplete(array $request): array
    {
        try {
            $clickTransId = $request['click_trans_id'] ?? '';
            $serviceId = $request['service_id'] ?? '';
            $clickPaydocId = $request['click_paydoc_id'] ?? '';
            $merchantTransId = $request['merchant_trans_id'] ?? '';
            $amount = $request['amount'] ?? 0;
            $action = $request['action'] ?? 0;
            $status = $request['status'] ?? 0; // 0 - success, -1 - cancelled
            $signTime = $request['sign_time'] ?? '';
            $signString = $request['sign_string'] ?? '';

            // Validate service_id
            if ((string)$serviceId !== (string)$this->serviceId) {
                return $this->error(-1, 'Service ID not found');
            }

            // Find payment
            $payment = Payment::find($merchantTransId);
            if (!$payment) {
                return $this->error(-5, 'Payment not found');
            }

            // Verify signature
            $expectedSign = md5(
                $clickTransId . $this->secretKey . $merchantTransId . $serviceId .
                $amount . $action . $signTime
            );

            if ($signString !== $expectedSign) {
                return $this->error(-1, 'Invalid signature');
            }

            if ((int)$status === 0) {
                // Payment successful
                $payment->complete($clickTransId);
                $payment->update([
                    'payment_method' => 'click',
                    'stripe_payment_id' => $clickTransId,
                ]);

                // Activate subscription if exists
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

                return $this->success($clickPaydocId, $merchantTransId);
            }

            // Payment cancelled
            $payment->update(['status' => 'cancelled']);

            return $this->success($clickPaydocId, $merchantTransId);
        } catch (\Exception $e) {
            Log::error('Click Complete error: ' . $e->getMessage());
            return $this->error(-99, 'Internal error');
        }
    }

    /**
     * Check payment status with Click API.
     */
    public function checkPaymentStatus(string $clickPaydocId): ?array
    {
        try {
            $response = Http::post("{$this->baseUrl}/merchant/payment/status", [
                'merchant_id' => $this->merchantId,
                'merchant_user_id' => $this->merchantUserId,
                'click_paydoc_id' => $clickPaydocId,
                'sign_time' => now()->timestamp,
                'sign_string' => md5($clickPaydocId . $this->secretKey),
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            Log::error('Click status check error: ' . $e->getMessage());
        }

        return null;
    }

    private function success($clickPaydocId, $merchantTransId): array
    {
        return [
            'success' => true,
            'error' => 0,
            'error_note' => 'Success',
            'click_paydoc_id' => $clickPaydocId,
            'merchant_trans_id' => $merchantTransId,
            'merchant_prepare_id' => $merchantTransId,
        ];
    }

    private function error(int $code, string $note): array
    {
        return [
            'success' => false,
            'error' => $code,
            'error_note' => $note,
        ];
    }
}
