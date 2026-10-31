<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    /**
     * List all subscription plans
     */
    public function plans(): JsonResponse
    {
        try {
            $plans = Plan::where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            return response()->json([
                'success' => true,
                'data' => PlanResource::collection($plans),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch plans',
            ], 500);
        }
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request, $planId): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $plan = Plan::findOrFail($planId);

            // Check for existing active subscription
            if ($user->hasActiveSubscription()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already have an active subscription',
                ], 409);
            }

            $paymentMethod = $request->input('payment_method', 'click');

            if (!in_array($paymentMethod, ['click', 'payze'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment method. Use click or payze.',
                ], 400);
            }

            $paymentData = [
                'payment_method' => $paymentMethod,
            ];

            $result = $this->paymentService->createSubscription($user, $plan, $paymentData);

            return response()->json([
                'success' => true,
                'message' => 'Subscription created. Redirect to payment URL.',
                'data' => [
                    'subscription' => $result['subscription'],
                    'payment' => $result['payment'],
                    'checkout_url' => $result['checkout_url'],
                ],
            ], 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Subscription failed',
            ], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $subscription = $user->activeSubscription();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription',
                ], 404);
            }

            $this->paymentService->cancelSubscription($subscription);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cancellation failed',
            ], 500);
        }
    }

    /**
     * Get user's subscriptions
     */
    public function mySubscriptions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $subscriptions = Subscription::with(['plan'])
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $subscriptions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscriptions',
            ], 500);
        }
    }

    /**
     * Get user's payment history
     */
    public function payments(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $payments = Payment::where('user_id', $user->id)
                ->latest()
                ->paginate($request->input('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $payments,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payments',
            ], 500);
        }
    }
}
