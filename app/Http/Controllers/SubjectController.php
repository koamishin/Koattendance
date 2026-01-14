<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    public function show(Request $request, $id)
    {
        $subject = Subject::withCount('students')->findOrFail($id);
        
        // Authorization check
        $user = Auth::user();
        if ($user->isTeacher() && $user->teacher && $subject->teacher_id !== $user->teacher->id) {
            abort(403);
        }

        $tab = $request->query('tab', 'overview');
        $students = [];
        
        if ($tab === 'students') {
            $students = $subject->students->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->first_name . ' ' . $s->last_name,
                'student_code' => $s->student_id,
                'qr_code' => $s->qr_code_data, // Useful if we need it
            ])->values();
        }

        return Inertia::render('Dashboard/Subjects/Show', [
            'subjectId' => $id,
            'initialSubject' => $subject,
            'initialStudents' => $students,
            'activeTab' => $tab,
        ]);
    }
}
