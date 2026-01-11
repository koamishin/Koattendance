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
                $gradeValue = $gradeObj ? (float) $gradeObj->grade : null;
                $record[$this->slugify($subject->name)] = $gradeValue;
                $record[$this->slugify($subject->name).'_id'] = $gradeObj ? $gradeObj->id : null;
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

    public function update(Grade $grade): JsonResponse
    {
        $validated = request()->validate([
            'grade' => 'required|numeric|min:0|max:100',
        ]);

        $grade->update(['grade' => $validated['grade']]);

        return response()->json([
            'message' => 'Grade updated successfully',
            'grade' => $grade,
        ]);
    }

    private function slugify(string $text): string
    {
        return strtolower(str_replace(' ', '_', $text));
    }
}
