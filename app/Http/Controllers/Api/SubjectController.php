<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    /**
     * Get or create a teacher record for the authenticated user.
     */
    private function getOrCreateTeacher(): ?Teacher
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isTeacher()) {
            return null;
        }

        if ($user->teacher) {
            return $user->teacher;
        }

        // Auto-create teacher record for users with teacher role
        $nameParts = explode(' ', $user->name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? '';

        return $user->teacher()->create([
            'employee_id' => 'T-' . strtoupper(Str::random(6)),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->email,
            'status' => 'active',
        ]);
    }

    public function index()
    {
        $teacher = $this->getOrCreateTeacher();

        if (!$teacher) {
            return response()->json(['message' => 'Only teachers can access subjects'], 403);
        }

        $subjects = $teacher->subjects()->withCount('students')->get();
        return response()->json($subjects);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
        ]);

        $teacher = $this->getOrCreateTeacher();

        if (!$teacher) {
            return response()->json(['message' => 'Only teachers can create subjects'], 403);
        }

        $subject = $teacher->subjects()->create([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
        ]);

        return response()->json($subject, 201);
    }

    public function show(Subject $subject)
    {
        $teacher = $this->getOrCreateTeacher();

        if (!$teacher || $subject->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $subject->loadCount('students');
        return response()->json($subject);
    }

    public function update(Request $request, Subject $subject)
    {
        $teacher = $this->getOrCreateTeacher();

        if (!$teacher || $subject->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
        ]);

        $subject->update($request->only('name', 'description', 'icon'));

        return response()->json($subject);
    }

    public function destroy(Subject $subject)
    {
        $teacher = $this->getOrCreateTeacher();

        if (!$teacher || $subject->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $subject->delete();

        return response()->json(['message' => 'Subject deleted']);
    }

    /**
     * Get all students with their QR codes for a subject.
     */
    public function studentsWithQrCodes(Subject $subject)
    {
        $teacher = $this->getOrCreateTeacher();

        if (!$teacher || $subject->teacher_id !== $teacher->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $students = $subject->students()->get()->map(function ($student) {
            // Ensure QR code exists
            if (!$student->qr_code_data) {
                $student->generateQrCode();
            }

            return [
                'id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'student_id' => $student->student_id,
                'qr_code' => $student->qr_code_data,
            ];
        });

        return response()->json([
            'subject' => [
                'id' => $subject->id,
                'name' => $subject->name,
            ],
            'students' => $students,
        ]);
    }
}
