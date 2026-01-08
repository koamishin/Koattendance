<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            GuardianSeeder::class,
            TeacherSeeder::class,
            CourseSeeder::class,
            SectionSeeder::class,
            StudentSeeder::class,
            GradeScaleSeeder::class,
            ClassSessionSeeder::class,
            AlertConfigurationSeeder::class,
            AttendanceRecordSeeder::class,
            GradeSeeder::class,
            AlertLogSeeder::class,
            SeatPlanSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
