<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PayzeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayzeController extends Controller
{
    public function __construct(
        private readonly PayzeService $payzeService
    ) {}

    /**
     * Handle Payze webhook (payment status updates).
     * Called by Payze when payment status changes.
     */
    public function hook(Request $request): JsonResponse
    {
        $processed = $this->payzeService->handleWebhook($request->all());

        return response()->json([
            'success' => $processed,
        ]);
    }

    /**
     * Success URL - user redirected here after successful Payze payment.
     */
    public function success(Request $request): JsonResponse
    {
        $paymentId = $request->input('payment_id');
        $payment = $paymentId ? Payment::find($paymentId) : null;

        return response()->json([
            'success' => true,
            'message' => 'Payment successful',
            'data' => [
                'payment_id' => $paymentId,
                'payment' => $payment,
            ],
        ]);
    }

    /**
     * Error URL - user redirected here after failed Payze payment.
     */
    public function error(Request $request): JsonResponse
    {
        $paymentId = $request->input('payment_id');
        if ($paymentId) {
            Payment::where('id', $paymentId)->update(['status' => 'failed']);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment failed or cancelled',
            'data' => [
                'payment_id' => $paymentId,
            ],
        ]);
    }

    /**
     * Refund a payment.
     */
    public function refund(int $paymentId): JsonResponse
    {
        try {
            $payment = Payment::findOrFail($paymentId);

            if ($payment->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment cannot be refunded',
                ], 400);
            }

            $refunded = $this->payzeService->refundPayment($payment);

            if ($refunded) {
                $payment->refund('Refunded via API');
                return response()->json([
                    'success' => true,
                    'message' => 'Payment refunded',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Refund failed',
            ], 500);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Refund failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
