<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GamificationSeeder extends Seeder
{
    public function run(): void
    {
        // XP History for demo student
        DB::table('xp_history')->insert([
            ['user_id' => 3, 'amount' => 100, 'source_type' => 'lesson_complete', 'source_id' => 1, 'description' => 'Completed lesson: Introduction to Web Security', 'multiplier' => 1.00, 'created_at' => now()->subDays(20)],
            ['user_id' => 3, 'amount' => 100, 'source_type' => 'lesson_complete', 'source_id' => 2, 'description' => 'Completed lesson: HTTP Basics', 'multiplier' => 1.00, 'created_at' => now()->subDays(19)],
            ['user_id' => 3, 'amount' => 200, 'source_type' => 'lab_complete', 'source_id' => 1, 'description' => 'Completed lab: SQL Injection Lab', 'multiplier' => 1.00, 'created_at' => now()->subDays(15)],
            ['user_id' => 3, 'amount' => 150, 'source_type' => 'lab_complete', 'source_id' => 2, 'description' => 'Completed lab: XSS Challenge', 'multiplier' => 1.00, 'created_at' => now()->subDays(10)],
            ['user_id' => 3, 'amount' => 250, 'source_type' => 'lab_complete', 'source_id' => 3, 'description' => 'Completed lab: Network Reconnaissance', 'multiplier' => 1.00, 'created_at' => now()->subDays(5)],
            ['user_id' => 3, 'amount' => 100, 'source_type' => 'quiz_complete', 'source_id' => 1, 'description' => 'Scored 90% on Web Security Quiz', 'multiplier' => 1.00, 'created_at' => now()->subDays(3)],
        ]);

        // Rankings
        DB::table('rankings')->insert([
            ['ranking_type' => 'global', 'user_id' => 1, 'period' => 'all_time', 'score' => 50000, 'rank' => 1, 'calculated_at' => now()],
            ['ranking_type' => 'global', 'user_id' => 2, 'period' => 'all_time', 'score' => 25000, 'rank' => 2, 'calculated_at' => now()],
            ['ranking_type' => 'global', 'user_id' => 3, 'period' => 'all_time', 'score' => 12500, 'rank' => 3, 'calculated_at' => now()],
        ]);

        // Notifications for demo student
        DB::table('notifications')->insert([
            [
                'user_id' => 3, 'type' => 'course_enrolled',
                'title' => 'Welcome to Web Application Security',
                'message' => 'You have been enrolled in Web Application Security course. Start learning today!',
                'priority' => 'normal', 'channel' => 'in_app',
                'read_at' => now()->subDays(14), 'sent_at' => now()->subDays(14),
                'created_at' => now()->subDays(14), 'updated_at' => now()->subDays(14),
            ],
            [
                'user_id' => 3, 'type' => 'badge_earned',
                'title' => 'New Badge: First Steps',
                'message' => 'Congratulations! You earned the First Steps badge.',
                'priority' => 'high', 'channel' => 'in_app',
                'read_at' => null, 'sent_at' => now()->subDays(5),
                'created_at' => now()->subDays(5), 'updated_at' => now()->subDays(5),
            ],
            [
                'user_id' => 3, 'type' => 'lab_available',
                'title' => 'New Lab Available',
                'message' => 'A new lab "SQL Injection Advanced" is now available for you.',
                'priority' => 'normal', 'channel' => 'in_app',
                'read_at' => null, 'sent_at' => now(),
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }
}
