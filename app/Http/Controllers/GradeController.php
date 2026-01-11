<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;

class GradeController extends Controller
{
    public function index(): JsonResponse
    {
        $subjects = Subject::with('grades')->get();

        $gradeRecords = [];
        $studentNames = Grade::pluck('student_name')->unique();

        foreach ($studentNames as $name) {
            $record = [
                'name' => $name,
            ];

            $grades = Grade::where('student_name', $name)->get();
            $totalGrade = 0;
            $gradeCount = 0;

            foreach ($subjects as $subject) {
                $gradeObj = $grades->firstWhere('subject_id', $subject->id);
                $record[$this->slugify($subject->name)] = $gradeObj ? (float) $gradeObj->grade : null;
                if ($gradeObj) {
                    $totalGrade += $gradeObj->grade;
                    $gradeCount++;
                }
            }

            $record['average'] = $gradeCount > 0 ? round($totalGrade / $gradeCount, 2) : 0;
            $gradeRecords[] = $record;
        }

        return response()->json([
            'gradeRecords' => $gradeRecords,
            'subjects' => $subjects->pluck('name')->map(fn ($name) => $this->slugify($name)),
        ]);
    }

    private function slugify(string $text): string
    {
        return strtolower(str_replace(' ', '_', $text));
    }
}
