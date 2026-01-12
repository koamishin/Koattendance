<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Subject;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class GradeController extends Controller
{
    public function index(): Response
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

        return Inertia::render('Dashboard/Grades', [
            'gradeRecords' => $gradeRecords,
            'subjects' => $subjects->pluck('name')->map(fn ($name) => $this->slugify($name))->toArray(),
        ]);
    }

    public function update(Grade $grade): RedirectResponse
    {
        $validated = request()->validate([
            'grade' => 'required|numeric|min:0|max:100',
        ]);

        $grade->update(['grade' => $validated['grade']]);

        return redirect()->back()->with('success', 'Grade updated successfully');
    }

    private function slugify(string $text): string
    {
        return strtolower(str_replace(' ', '_', $text));
    }
}
