<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin User',
            'email' => 'admin@cyberlab.academy',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_roles')->insert([
            'user_id' => $adminId,
            'role_id' => 1, // Super Admin
        ]);

        DB::table('user_xp_summary')->insert([
            'user_id' => $adminId,
            'total_xp' => 50000,
            'level' => 9,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Demo instructor
        $instructorId = DB::table('users')->insertGetId([
            'name' => 'John Instructor',
            'email' => 'instructor@cyberlab.academy',
            'username' => 'instructor',
            'password' => Hash::make('instructor123'),
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_roles')->insert([
            'user_id' => $instructorId,
            'role_id' => 3, // Instructor
        ]);

        DB::table('user_xp_summary')->insert([
            'user_id' => $instructorId,
            'total_xp' => 25000,
            'level' => 8,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Demo student
        $studentId = DB::table('users')->insertGetId([
            'name' => 'Demo Student',
            'email' => 'student@cyberlab.academy',
            'username' => 'student',
            'password' => Hash::make('student123'),
            'is_active' => true,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('user_roles')->insert([
            'user_id' => $studentId,
            'role_id' => 4, // Student
        ]);

        DB::table('user_xp_summary')->insert([
            'user_id' => $studentId,
            'total_xp' => 12500,
            'level' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
