<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Insert roles
        DB::table('roles')->insert([
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full system access', 'is_system' => true],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Administrative access', 'is_system' => true],
            ['name' => 'Instructor', 'slug' => 'instructor', 'description' => 'Course creation and management', 'is_system' => true],
            ['name' => 'Student', 'slug' => 'student', 'description' => 'Regular student access', 'is_system' => true],
        ]);

        // Insert permissions
        DB::table('permissions')->insert([
            ['name' => 'Manage Users', 'slug' => 'manage-users', 'module' => 'User Management'],
            ['name' => 'Manage Courses', 'slug' => 'manage-courses', 'module' => 'Course Management'],
            ['name' => 'Manage Labs', 'slug' => 'manage-labs', 'module' => 'Lab Management'],
            ['name' => 'Manage Quizzes', 'slug' => 'manage-quizzes', 'module' => 'Quiz Management'],
            ['name' => 'View Analytics', 'slug' => 'view-analytics', 'module' => 'Analytics'],
            ['name' => 'Manage Payments', 'slug' => 'manage-payments', 'module' => 'Payment Management'],
            ['name' => 'Manage Certificates', 'slug' => 'manage-certificates', 'module' => 'Certificate Management'],
            ['name' => 'Enroll Courses', 'slug' => 'enroll-courses', 'module' => 'Learning'],
            ['name' => 'Access Labs', 'slug' => 'access-labs', 'module' => 'Learning'],
            ['name' => 'Take Quizzes', 'slug' => 'take-quizzes', 'module' => 'Learning'],
        ]);

        // Insert levels
        DB::table('levels')->insert([
            ['level' => 1, 'title' => 'Newbie', 'min_xp' => 0, 'max_xp' => 99],
            ['level' => 2, 'title' => 'Beginner', 'min_xp' => 100, 'max_xp' => 499],
            ['level' => 3, 'title' => 'Apprentice', 'min_xp' => 500, 'max_xp' => 1499],
            ['level' => 4, 'title' => 'Intermediate', 'min_xp' => 1500, 'max_xp' => 2999],
            ['level' => 5, 'title' => 'Practitioner', 'min_xp' => 3000, 'max_xp' => 5999],
            ['level' => 6, 'title' => 'Advanced', 'min_xp' => 6000, 'max_xp' => 9999],
            ['level' => 7, 'title' => 'Expert', 'min_xp' => 10000, 'max_xp' => 14999],
            ['level' => 8, 'title' => 'Master', 'min_xp' => 15000, 'max_xp' => 24999],
            ['level' => 9, 'title' => 'Grandmaster', 'min_xp' => 25000, 'max_xp' => 39999],
            ['level' => 10, 'title' => 'Legend', 'min_xp' => 40000, 'max_xp' => 999999],
        ]);

        // Insert subscription plans
        DB::table('plans')->insert([            ['name' => 'Free',
                'slug' => 'free',
                'description' => 'Basic access to free courses',
                'price' => 0,
                'currency' => 'USD',
                'billing_period' => 'monthly',
                'features' => json_encode(['access_to_free_courses' => true, 'max_labs_per_day' => 3]),
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pro Monthly',
                'slug' => 'pro-monthly',
                'description' => 'Full access to all courses and labs',
                'price' => 29.99,
                'currency' => 'USD',
                'billing_period' => 'monthly',
                'features' => json_encode(['access_to_all_courses' => true, 'unlimited_labs' => true, 'certificate_download' => true, 'priority_support' => true]),
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Pro Yearly',
                'slug' => 'pro-yearly',
                'description' => 'Full access with 2 months free',
                'price' => 299.99,
                'currency' => 'USD',
                'billing_period' => 'yearly',
                'features' => json_encode(['access_to_all_courses' => true, 'unlimited_labs' => true, 'certificate_download' => true, 'priority_support' => true, 'savings_20_percent' => true]),
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For teams and organizations',
                'price' => 499.99,
                'currency' => 'USD',
                'billing_period' => 'monthly',
                'features' => json_encode(['access_to_all_courses' => true, 'unlimited_labs' => true, 'team_management' => true, 'custom_content' => true, 'api_access' => true, 'dedicated_support' => true]),
                'is_active' => true,
                'sort_order' => 4,
            ],
        ]);
    }
}
