<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            ['code' => 'MATH101', 'name' => 'Mathematics', 'description' => 'Basic mathematics course', 'grade_level' => 1, 'credits' => 3, 'type' => 'core'],
            ['code' => 'ENG101', 'name' => 'English Language Arts', 'description' => 'English language and literature', 'grade_level' => 1, 'credits' => 3, 'type' => 'core'],
            ['code' => 'SCI101', 'name' => 'Science', 'description' => 'General science course', 'grade_level' => 1, 'credits' => 3, 'type' => 'core'],
            ['code' => 'HIST101', 'name' => 'History', 'description' => 'World history', 'grade_level' => 1, 'credits' => 2, 'type' => 'core'],
            ['code' => 'PE101', 'name' => 'Physical Education', 'description' => 'Physical education and health', 'grade_level' => 1, 'credits' => 1, 'type' => 'required'],
            ['code' => 'ART101', 'name' => 'Art', 'description' => 'Visual arts and creativity', 'grade_level' => 1, 'credits' => 1, 'type' => 'elective'],
            ['code' => 'MATH201', 'name' => 'Algebra I', 'description' => 'Introduction to algebra', 'grade_level' => 2, 'credits' => 3, 'type' => 'core'],
            ['code' => 'SCI201', 'name' => 'Biology', 'description' => 'Introduction to biology', 'grade_level' => 2, 'credits' => 3, 'type' => 'core'],
            ['code' => 'ENG201', 'name' => 'Literature', 'description' => 'English literature', 'grade_level' => 2, 'credits' => 3, 'type' => 'core'],
            ['code' => 'CS101', 'name' => 'Computer Science', 'description' => 'Introduction to programming', 'grade_level' => 2, 'credits' => 2, 'type' => 'elective'],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
