<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\User;

class PaymentService
{
    public function __construct(
        private readonly ClickService $clickService,
        private readonly PayzeService $payzeService
    ) {}

    /**
     * Get available plans.
     */
    public function getPlans(): \Illuminate\Database\Eloquent\Collection
    {
        return Plan::active()->ordered()->get();
    }

    /**
     * Create subscription and initiate payment.
     * Returns payment URL for redirect.
     */
    public function createSubscription(User $user, Plan $plan, array $paymentData): array
    {
        // Check for existing active subscription
        $existing = $user->subscriptions()
            ->where('status', 'active')
            ->where('current_period_end', '>', now())
            ->first();

        if ($existing) {
            throw new \Exception('User already has an active subscription');
        }

        // FREE PLAN: activate immediately without payment
        if ((float) $plan->price === 0.0) {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => null,
                'payment_method' => null,
            ]);

            return [
                'subscription' => $subscription->fresh()->load('plan'),
                'payment' => null,
                'checkout_url' => null,
            ];
        }

        $paymentMethod = $paymentData['payment_method'] ?? 'click';

        if (!in_array($paymentMethod, ['click', 'payze'])) {
            throw new \Exception('Invalid payment method. Use click or payze.');
        }

        // Create subscription first (pending)
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'pending',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
            'payment_method' => $paymentData,
        ]);

        // Create payment
        $payment = Payment::create([
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
            'amount' => $plan->price,
            'currency' => $plan->currency,
            'status' => 'pending',
            'payment_method' => $paymentMethod,
            'description' => "Subscription to {$plan->name}",
        ]);

        $checkoutUrl = null;

        // Process payment with selected gateway
        switch ($paymentMethod) {
            case 'click':
                $checkoutUrl = $this->clickService->generatePaymentUrl($payment, $user);
                break;

            case 'payze':
                $result = $this->payzeService->createPayment($payment, $user);
                $checkoutUrl = $result['checkout_url'] ?? null;
                break;

            default:
                throw new \Exception('Unsupported payment method');
        }

        return [
            'subscription' => $subscription->fresh()->load('plan'),
            'payment' => $payment->fresh(),
            'checkout_url' => $checkoutUrl,
        ];
    }

    /**
     * Cancel subscription.
     */
    public function cancelSubscription(Subscription $subscription): Subscription
    {
        $subscription->cancel();

        // Send notification
        \App\Models\Notification::send(
            $subscription->user,
            'subscription_cancelled',
            'Subscription Cancelled',
            'Your subscription has been cancelled.'
        );

        return $subscription;
    }

    /**
     * Renew subscription.
     */
    public function renewSubscription(Subscription $subscription): array
    {
        if (!$subscription->plan) {
            throw new \Exception('Subscription has no associated plan');
        }

        $payment = Payment::create([
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'amount' => $subscription->plan->price,
            'currency' => $subscription->plan->currency,
            'status' => 'pending',
            'payment_method' => $subscription->payment_method ?? 'click',
            'description' => "Renewal of {$subscription->plan->name}",
        ]);

        $user = $subscription->user;
        $checkoutUrl = null;

        switch ($payment->payment_method) {
            case 'click':
                $checkoutUrl = $this->clickService->generatePaymentUrl($payment, $user);
                break;

            case 'payze':
                $result = $this->payzeService->createPayment($payment, $user);
                $checkoutUrl = $result['checkout_url'] ?? null;
                break;
        }

        return [
            'payment' => $payment->fresh(),
            'checkout_url' => $checkoutUrl,
        ];
    }

    /**
     * Get user's payment history.
     */
    public function getPaymentHistory(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return Payment::where('user_id', $user->id)
            ->with('subscription.plan')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Refund payment.
     */
    public function refund(Payment $payment, string $reason = null): Payment
    {
        // Try Payze refund first
        if ($payment->payment_method === 'payze' && $payment->stripe_payment_id) {
            $this->payzeService->refundPayment($payment);
        }

        $payment->refund($reason);

        // Update subscription if applicable
        if ($payment->subscription_id) {
            $payment->subscription->cancel();
        }

        return $payment;
    }
}
