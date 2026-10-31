<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Http\Controllers\Controller;
use App\Services\ClickService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClickController extends Controller
{
    public function __construct(
        private readonly ClickService $clickService
    ) {}

    /**
     * Handle Click Prepare request (validate order before payment).
     * Called by Click before showing payment page.
     */
    public function prepare(Request $request): JsonResponse
    {
        $result = $this->clickService->handlePrepare($request->all());

        return response()->json($result);
    }

    /**
     * Handle Click Complete request (confirm payment result).
     * Called by Click after payment is processed.
     */
    public function complete(Request $request): JsonResponse
    {
        $result = $this->clickService->handleComplete($request->all());

        return response()->json($result);
    }

    /**
     * Return URL - user redirected here after Click payment.
     */
    public function returnUrl(Request $request): JsonResponse
    {
        $status = $request->input('status', 'pending');
        $paymentId = $request->input('transaction_param');

        return response()->json([
            'success' => $status === '0',
            'message' => $status === '0' ? 'Payment successful' : 'Payment cancelled',
            'data' => [
                'payment_id' => $paymentId,
                'status' => $status === '0' ? 'completed' : 'cancelled',
            ],
        ]);
    }

    /**
     * Callback URL - Click sends payment notification here.
     */
    public function callback(Request $request): JsonResponse
    {
        // Log the callback for debugging
        \Illuminate\Support\Facades\Log::info('Click callback received', $request->all());

        return $this->complete($request);
    }
}
