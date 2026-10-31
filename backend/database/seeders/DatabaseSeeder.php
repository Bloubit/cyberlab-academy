<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            CourseSeeder::class,
            LabSeeder::class,
            LabInstanceSeeder::class,
            GamificationSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
