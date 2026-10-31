<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Demo subscription for admin user
        DB::table('subscriptions')->insert([
            'user_id' => 1,
            'plan_id' => 2, // Pro Monthly
            'status' => 'active',
            'trial_start' => null,
            'trial_end' => null,
            'current_period_start' => now()->subDays(30),
            'current_period_end' => now()->addDays(30),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Demo payment
        DB::table('payments')->insert([
            'user_id' => 1,
            'subscription_id' => 1,
            'amount' => 29.99,
            'currency' => 'USD',
            'status' => 'completed',
            'payment_method' => 'stripe',
            'stripe_payment_id' => 'pi_' . uniqid(),
            'description' => 'Pro Monthly subscription',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
