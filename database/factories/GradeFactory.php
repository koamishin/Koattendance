<?php

namespace Database\Factories;

use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    public function definition(): array
    {
        $score = fake()->numberBetween(0, 100);
        $maxScore = fake()->randomElement([100, 50, 25, 20, 10]);

        return [
            'student_id' => Student::factory(),
            'course_id' => Course::factory(),
            'class_session_id' => ClassSession::factory(),
            'grade_type' => fake()->randomElement(['assignment', 'quiz', 'exam', 'project', 'participation', 'final']),
            'grade_item' => fake()->randomElement(['Homework 1', 'Quiz 1', 'Midterm Exam', 'Final Project', 'Class Participation']),
            'score' => $score,
            'max_score' => $maxScore,
            'percentage' => ($score / $maxScore) * 100,
            'grade_letter' => fake()->randomElement(['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'F']),
            'weight' => fake()->randomElement([1, 1.5, 2, 2.5, 3]),
            'academic_year' => fake()->randomElement(['2024-2025', '2025-2026']),
            'semester' => fake()->randomElement(['first', 'second']),
            'grading_period' => fake()->randomElement(['midterm', 'final', 'quarter']),
            'feedback' => fake()->optional()->paragraph(),
            'recorded_by' => User::factory(),
        ];
    }
}
