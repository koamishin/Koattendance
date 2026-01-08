<?php

namespace Database\Seeders;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $guardians = Guardian::all();

        $students = [
            ['first_name' => 'John', 'last_name' => 'Smith', 'gender' => 'male', 'current_grade_level' => 1],
            ['first_name' => 'Jane', 'last_name' => 'Doe', 'gender' => 'female', 'current_grade_level' => 1],
            ['first_name' => 'Michael', 'last_name' => 'Johnson', 'gender' => 'male', 'current_grade_level' => 1],
            ['first_name' => 'Emily', 'last_name' => 'Williams', 'gender' => 'female', 'current_grade_level' => 1],
            ['first_name' => 'David', 'last_name' => 'Brown', 'gender' => 'male', 'current_grade_level' => 2],
            ['first_name' => 'Sarah', 'last_name' => 'Davis', 'gender' => 'female', 'current_grade_level' => 2],
            ['first_name' => 'James', 'last_name' => 'Miller', 'gender' => 'male', 'current_grade_level' => 2],
            ['first_name' => 'Jennifer', 'last_name' => 'Wilson', 'gender' => 'female', 'current_grade_level' => 2],
            ['first_name' => 'Robert', 'last_name' => 'Moore', 'gender' => 'male', 'current_grade_level' => 3],
            ['first_name' => 'Lisa', 'last_name' => 'Taylor', 'gender' => 'female', 'current_grade_level' => 3],
        ];

        foreach ($students as $index => $studentData) {
            $user = User::factory()->student()->create([
                'name' => "{$studentData['first_name']} {$studentData['last_name']}",
                'email' => strtolower($studentData['first_name']).'.'.strtolower($studentData['last_name']).'@student.koatendance.com',
            ]);

            $student = new Student;
            $student->user_id = $user->id;
            $student->student_id = 'STU-'.strtoupper(sprintf('%04d', $index + 1));
            $student->first_name = $studentData['first_name'];
            $student->last_name = $studentData['last_name'];
            $student->gender = $studentData['gender'];
            $student->current_grade_level = $studentData['current_grade_level'];
            $student->birth_date = \Carbon\Carbon::now()->subYears(rand(6, 18))->toDateString();
            $student->phone = fake()->phoneNumber();
            $student->address = fake()->address();
            $student->status = 'active';
            $student->enrollment_date = \Carbon\Carbon::now()->subYears(rand(0, 2))->toDateString();
            $student->qr_code_data = fake()->uuid();
            $student->qr_code_active = true;
            $student->qr_code_regenerated_at = now();

            if ($guardians->isNotEmpty()) {
                $student->guardian_id = $guardians->random()->id;
            }

            $student->save();
        }
    }
}
