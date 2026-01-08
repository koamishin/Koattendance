<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@koatendance.com',
        ]);

        User::factory()->teacher()->create([
            'name' => 'Teacher User',
            'email' => 'teacher@koatendance.com',
        ]);

        User::factory()->student()->create([
            'name' => 'Student User',
            'email' => 'student@koatendance.com',
        ]);

        User::factory()->guardian()->create([
            'name' => 'Guardian User',
            'email' => 'guardian@koatendance.com',
        ]);
    }
}
