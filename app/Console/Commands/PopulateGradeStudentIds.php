<?php

namespace App\Console\Commands;

use App\Models\Grade;
use App\Models\Student;
use Illuminate\Console\Command;

class PopulateGradeStudentIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:populate-grade-student-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate student_id in grades table based on student_name';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $grades = Grade::whereNull('student_id')->get();
        $updated = 0;

        foreach ($grades as $grade) {
            $student = Student::where('name', $grade->student_name)->first();
            if ($student) {
                $grade->update(['student_id' => $student->id]);
                $updated++;
            }
        }

        $this->info("Updated {$updated} grades with student IDs.");

        return Command::SUCCESS;
    }
}
