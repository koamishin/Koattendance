<?php

namespace Database\Seeders;

use App\Models\SeatAllocation;
use App\Models\SeatPlan;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Seeder;

class SeatPlanSeeder extends Seeder
{
    public function run(): void
    {
        $sections = Section::all();
        $students = Student::all();

        if ($sections->isEmpty() || $students->isEmpty()) {
            return;
        }

        foreach ($sections->take(2) as $section) {
            $seatPlan = SeatPlan::create([
                'section_id' => $section->id,
                'name' => 'Classroom Layout A',
                'layout' => [
                    'rows' => 5,
                    'columns' => 6,
                    ' arrangement' => 'grid',
                ],
            ]);

            $sectionStudents = $students->where('current_grade_level', $section->grade_level)->take(min(20, $section->max_students));

            $row = 1;
            $col = 1;
            foreach ($sectionStudents as $index => $student) {
                SeatAllocation::create([
                    'seat_plan_id' => $seatPlan->id,
                    'student_id' => $student->id,
                    'row' => $row,
                    'column' => $col,
                    'seat_label' => chr(64 + $row).$col,
                ]);

                $col++;
                if ($col > 6) {
                    $col = 1;
                    $row++;
                }
            }
        }
    }
}
