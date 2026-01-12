<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::all();
        $students = Student::all();
        $teachers = User::where('role', 'teacher')->get();

        if ($courses->isEmpty() || $students->isEmpty()) {
            return;
        }

        $gradeTypes = ['assignment', 'quiz', 'exam', 'project', 'participation'];
        $gradeItems = [
            'assignment' => ['Homework 1', 'Homework 2', 'Homework 3', 'Worksheet 1', 'Worksheet 2'],
            'quiz' => ['Quiz 1', 'Quiz 2', 'Pop Quiz', 'Weekly Quiz'],
            'exam' => ['Midterm Exam', 'Final Exam', 'Unit Test'],
            'project' => ['Group Project', 'Individual Project', 'Presentation'],
            'participation' => ['Class Participation', 'Discussion Contribution'],
        ];

        foreach ($courses->take(5) as $course) {
            foreach ($students->take(5) as $student) {
                foreach ($gradeTypes as $gradeType) {
                    $items = $gradeItems[$gradeType];
                    foreach ($items as $item) {
                        $score = fake()->numberBetween(50, 100);
                        $maxScore = fake()->randomElement([100, 50, 25, 20]);

                        $percentage = ($score / $maxScore) * 100;
                        $gradeLetter = $this->getGradeLetter($percentage);

                        \App\Models\Grade::create([
                            'student_id' => $student->id,
                            'course_id' => $course->id,
                            'class_session_id' => null,
                            'grade_type' => $gradeType,
                            'grade_item' => $item,
                            'score' => $score,
                            'max_score' => $maxScore,
                            'percentage' => $percentage,
                            'grade_letter' => $gradeLetter,
                            'weight' => fake()->randomElement([1, 1.5, 2]),
                            'academic_year' => '2024-2025',
                            'semester' => 'first',
                            'grading_period' => fake()->randomElement(['midterm', 'final', 'quarter']),
                            'feedback' => fake()->optional()->sentence(),
                            'recorded_by' => $teachers->random()?->id,
                        ]);
                    }
                }
            }
        }
    }

    private function getGradeLetter(float $percentage): string
    {
        if ($percentage >= 93) {
            return 'A';
        }
        if ($percentage >= 90) {
            return 'A-';
        }
        if ($percentage >= 87) {
            return 'B+';
        }
        if ($percentage >= 83) {
            return 'B';
        }
        if ($percentage >= 80) {
            return 'B-';
        }
        if ($percentage >= 77) {
            return 'C+';
        }
        if ($percentage >= 73) {
            return 'C';
        }
        if ($percentage >= 70) {
            return 'C-';
        }
        if ($percentage >= 67) {
            return 'D+';
        }
        if ($percentage >= 63) {
            return 'D';
        }

        return 'F';
    }
}
