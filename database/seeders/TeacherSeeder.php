<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            ['first_name' => 'Robert', 'last_name' => 'Anderson', 'department' => 'Mathematics'],
            ['first_name' => 'Amanda', 'last_name' => 'Thomas', 'department' => 'English'],
            ['first_name' => 'Christopher', 'last_name' => 'Jackson', 'department' => 'Science'],
            ['first_name' => 'Michelle', 'last_name' => 'White', 'department' => 'History'],
            ['first_name' => 'Daniel', 'last_name' => 'Harris', 'department' => 'Physical Education'],
        ];

        foreach ($teachers as $index => $teacherData) {
            $user = User::factory()->teacher()->create([
                'name' => "{$teacherData['first_name']} {$teacherData['last_name']}",
                'email' => strtolower($teacherData['first_name']).'.'.strtolower($teacherData['last_name']).'@koatendance.com',
            ]);

            $teacher = new \App\Models\Teacher;
            $teacher->user_id = $user->id;
            $teacher->employee_id = 'TCH-'.strtoupper(sprintf('%04d', $index + 1));
            $teacher->first_name = $teacherData['first_name'];
            $teacher->last_name = $teacherData['last_name'];
            $teacher->email = $user->email;
            $teacher->phone = fake()->phoneNumber();
            $teacher->department = $teacherData['department'];
            $teacher->status = 'active';
            $teacher->save();
        }
    }
}
