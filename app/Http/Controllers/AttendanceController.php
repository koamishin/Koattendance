<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function updateStatus(string $id, Request $request): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
    {
        if ($id === 'null') {
            $validated = $request->validate([
                'status' => 'required|in:present,late,absent',
                'student_id' => 'required|exists:students,id',
                'session_id' => 'nullable|exists:class_sessions,id',
                'subjectId' => 'nullable|exists:subjects,id',
                'date' => 'nullable|date',
            ]);

            $studentId = (int) $validated['student_id'];

            $session = null;
            if (! empty($validated['session_id'])) {
                $session = ClassSession::findOrFail((int) $validated['session_id']);
            } else {
                $user = Auth::user();
                $teacherId = $user?->teacher?->id;
                $subjectId = ! empty($validated['subjectId']) ? (int) $validated['subjectId'] : null;
                $scheduledDate = ! empty($validated['date'])
                    ? \Carbon\Carbon::parse($validated['date'])->startOfDay()
                    : \Carbon\Carbon::today();

                if (! $teacherId || ! $subjectId) {
                    return back()->with('error', 'Unable to determine session for attendance entry');
                }

                $subject = Subject::find($subjectId);
                $course = Course::firstOrCreate(
                    ['code' => 'SUBJ-'.$subjectId],
                    [
                        'name' => $subject?->name ?? 'Subject '.$subjectId,
                        'description' => $subject?->description,
                        'grade_level' => 1,
                        'credits' => 1,
                        'type' => 'core',
                        'is_active' => true,
                    ],
                );

                $status = $scheduledDate->isPast() ? 'completed' : 'in_progress';

                $session = ClassSession::firstOrCreate(
                    [
                        'teacher_id' => $teacherId,
                        'subject_id' => $subjectId,
                        'scheduled_date' => $scheduledDate,
                    ],
                    [
                        'course_id' => $course->id,
                        'section_id' => null,
                        'room' => 'Default',
                        'start_time' => now()->format('H:i:s'),
                        'end_time' => now()->addHours(2)->format('H:i:s'),
                        'status' => $status,
                        'attendance_mode' => 'manual',
                        'late_threshold_minutes' => 15,
                    ],
                );
            }

            if ($session->subject_id && ! empty($validated['subjectId']) && (int) $validated['subjectId'] !== (int) $session->subject_id) {
                return back()->with('error', 'Session does not match subject');
            }

            $record = AttendanceRecord::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'student_id' => $studentId,
                ],
                [
                    'subject_id' => $session->subject_id ?? (! empty($validated['subjectId']) ? (int) $validated['subjectId'] : null),
                    'status' => $validated['status'],
                    'timestamp' => now(),
                    'recorded_by' => Auth::id(),
                ],
            );

            return back()->with('success', $record->wasRecentlyCreated ? 'Attendance marked successfully' : 'Attendance updated successfully');
        }

        $validated = $request->validate([
            'status' => 'required|in:present,late,absent',
        ]);

        $record = AttendanceRecord::findOrFail($id);

        $record->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Attendance updated successfully');
    }

    public function index(): JsonResponse
    {
        $selectedSubjectId = request('subjectId') ? (int) request('subjectId') : null;
        $selectedDate = request('date')
            ? \Carbon\Carbon::parse(request('date'))->startOfDay()
            : \Carbon\Carbon::today();

        $user = Auth::user();
        $teacherId = $user?->teacher?->id;

        $enrolledStudents = collect();
        if ($selectedSubjectId) {
            $subject = Subject::with('students')->find($selectedSubjectId);
            if ($subject) {
                $enrolledStudents = $subject->students;
            }
        } else {
            $enrolledStudents = collect();
        }

        $session = null;
        if ($selectedSubjectId) {
            $courseId = Course::where('code', 'SUBJ-'.$selectedSubjectId)->value('id');
            $sessionQuery = ClassSession::query()
                ->whereDate('scheduled_date', $selectedDate)
                ->where(function ($query) use ($selectedSubjectId, $courseId) {
                    $query->where('subject_id', $selectedSubjectId)
                        ->orWhere('course_id', $selectedSubjectId);

                    if ($courseId) {
                        $query->orWhere('course_id', $courseId);
                    }
                });

            if ($teacherId) {
                $sessionQuery->where('teacher_id', $teacherId);
            }

            $session = (clone $sessionQuery)
                ->where('status', 'in_progress')
                ->latest('id')
                ->first();

            if (! $session) {
                $session = $sessionQuery->latest('id')->first();
            }
        }

        $attendanceRecords = $session
            ? AttendanceRecord::query()
                ->with('student')
                ->where('session_id', $session->id)
                ->get()
                ->keyBy('student_id')
            : collect();

        // 3. Merge Enrolled Students with Attendance Records
        $mergedRecords = $enrolledStudents->map(function ($student) use ($attendanceRecords, $selectedDate) {
            $record = $attendanceRecords->get($student->id);

            if ($record) {
                return [
                    'id' => $record->id,
                    'student_id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name, // Using new fields
                    'status' => $record->status,
                    'date' => $record->timestamp->format('M d, Y'),
                    'dayOfWeek' => $record->timestamp->format('l'),
                    'time' => $record->status === 'absent' ? '-' : ($record->timestamp ? $record->timestamp->format('h:i A') : '-'),
                ];
            }

            return [
                'id' => null, // No record ID yet
                'student_id' => $student->id,
                'name' => $student->first_name . ' ' . $student->last_name,
                'status' => 'unmarked',
                'date' => $selectedDate->format('M d, Y'),
                'dayOfWeek' => $selectedDate->format('l'),
                'time' => '-',
            ];
        })->sortBy('name')->values();

        // Stats calculation based on merged records (enrolled students)
        $stats = [
            'present' => $mergedRecords->where('status', 'present')->count(),
            'absent' => $mergedRecords->where('status', 'absent')->count(),
            'late' => $mergedRecords->where('status', 'late')->count(),
            'unmarked' => $mergedRecords->where('status', 'unmarked')->count(),
            'total' => $mergedRecords->count(),
        ];

        $today = \Carbon\Carbon::today();
        $availableDates = [];
        for ($i = -30; $i <= 30; $i++) {
            $availableDates[] = $today->copy()->addDays($i)->format('Y-m-d');
        }

        $existingSessionDates = [];
        if ($selectedSubjectId) {
            $courseId = Course::where('code', 'SUBJ-'.$selectedSubjectId)->value('id');
            $existingSessionDates = ClassSession::query()
                ->where(function ($query) use ($selectedSubjectId, $courseId) {
                    $query->where('subject_id', $selectedSubjectId)
                        ->orWhere('course_id', $selectedSubjectId);

                    if ($courseId) {
                        $query->orWhere('course_id', $courseId);
                    }
                })
                ->when($teacherId, fn ($q) => $q->where('teacher_id', $teacherId))
                ->pluck('scheduled_date')
                ->map(fn ($d) => $d instanceof \Carbon\Carbon ? $d->format('Y-m-d') : \Carbon\Carbon::parse($d)->format('Y-m-d'))
                ->toArray();
        }

        $availableDates = array_unique(array_merge($existingSessionDates, $availableDates));
        rsort($availableDates);

        // Get all subjects for dropdown
        $subjects = Subject::all()->map(fn ($subject) => [
            'id' => $subject->id,
            'name' => $subject->name,
            'students_count' => $subject->students()->count(),
        ])->values();

        return response()->json([
            'attendanceRecords' => $mergedRecords,
            'groupedRecords' => [],
            'stats' => $stats,
            'selectedDate' => $selectedDate->format('Y-m-d'),
            'availableDates' => array_values($availableDates),
            'subjects' => $subjects,
            'selectedSubjectId' => $selectedSubjectId,
            'session' => $session ? [
                'id' => $session->id,
                'status' => $session->status,
                'scheduled_date' => $session->scheduled_date?->format('Y-m-d'),
                'late_threshold_minutes' => $session->late_threshold_minutes,
            ] : null,
        ]);
    }

    /**
     * Enroll a student in a subject.
     */
    public function enroll(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'student_id' => 'required|exists:students,id',
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        
        // Check if already attached
        if (!$subject->students()->where('student_id', $request->student_id)->exists()) {
            $subject->students()->attach($request->student_id);
            return back()->with('success', 'Student added to class successfully.');
        }

        return back()->with('info', 'Student is already enrolled in this class.');
    }
    
    /**
     * Search students not enrolled in a subject.
     */
    public function searchStudents(Request $request)
    {
        $search = $request->get('query');
        $subjectId = $request->get('subject_id');

        return Student::query()
            ->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('student_id', 'like', "%{$search}%");
            })
            ->whereDoesntHave('subjects', function($q) use ($subjectId) {
                $q->where('subjects.id', $subjectId);
            })
            ->limit(10)
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'student_id' => $student->student_id,
                ];
            });
    }
}
