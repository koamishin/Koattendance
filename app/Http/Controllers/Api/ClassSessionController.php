<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClassSessionController extends Controller
{
    /**
     * Get today's sessions for the current teacher.
     */
    public function today()
    {
        $user = Auth::user();
        if (!$user->teacher) {
            return response()->json(['sessions' => []]);
        }

        $sessions = ClassSession::with(['course', 'section'])
            ->where('teacher_id', $user->teacher->id)
            ->whereDate('scheduled_date', today())
            ->get();

        return response()->json(['sessions' => $sessions]);
    }

    /**
     * Start or get an existing session for a subject.
     */
    public function startForSubject(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'late_threshold_minutes' => 'nullable|integer|min:1|max:120',
        ]);

        $user = Auth::user();
        if (!$user->teacher) {
            return response()->json(['message' => 'Only teachers can start sessions'], 403);
        }

        $subject = Subject::findOrFail($request->subject_id);

        // Verify ownership
        if ($subject->teacher_id !== $user->teacher->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check for existing active session today
        $existingSession = ClassSession::where('teacher_id', $user->teacher->id)
            ->where('course_id', $subject->id) // Using course_id to store subject_id
            ->whereDate('scheduled_date', today())
            ->where('status', 'in_progress')
            ->first();

        if ($existingSession) {
            return response()->json([
                'session' => $existingSession,
                'message' => 'Existing session found',
                'is_new' => false,
            ]);
        }

        // Create new session
        $session = ClassSession::create([
            'teacher_id' => $user->teacher->id,
            'course_id' => $subject->id, // Storing subject_id in course_id
            'section_id' => null,
            'room' => 'Default',
            'scheduled_date' => today(),
            'start_time' => now()->format('H:i:s'),
            'end_time' => now()->addHours(2)->format('H:i:s'),
            'status' => 'in_progress',
            'attendance_mode' => 'qr_scan',
            'late_threshold_minutes' => $request->late_threshold_minutes ?? 15,
        ]);

        return response()->json([
            'session' => $session,
            'message' => 'Session started successfully',
            'is_new' => true,
        ]);
    }

    /**
     * End a session and mark all unmarked students as absent.
     */
    public function endSession(Request $request, ClassSession $session)
    {
        $user = Auth::user();
        if (!$user->teacher || $session->teacher_id !== $user->teacher->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($session->status === 'completed') {
            return response()->json(['message' => 'Session already ended'], 400);
        }

        // Get the subject/course ID
        $subjectId = $session->course_id;

        // Get all students enrolled in this subject
        $subject = Subject::with('students')->find($subjectId);
        
        if (!$subject) {
            return response()->json(['message' => 'Subject not found'], 404);
        }

        $enrolledStudentIds = $subject->students->pluck('id')->toArray();

        // Get students who already have attendance records for this session
        $presentStudentIds = AttendanceRecord::where('session_id', $session->id)
            ->pluck('student_id')
            ->toArray();

        // Find students who haven't been marked (unmarked = absent)
        $absentStudentIds = array_diff($enrolledStudentIds, $presentStudentIds);

        $markedAbsentCount = 0;

        DB::transaction(function () use ($session, $absentStudentIds, $subjectId, &$markedAbsentCount) {
            foreach ($absentStudentIds as $studentId) {
                AttendanceRecord::create([
                    'session_id' => $session->id,
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'status' => 'absent',
                    'timestamp' => now(),
                    'recorded_by' => Auth::id(),
                    'notes' => 'Auto-marked absent at session end',
                ]);
                $markedAbsentCount++;
            }

            // Update session status
            $session->update([
                'status' => 'completed',
                'end_time' => now()->format('H:i:s'),
            ]);
        });

        return response()->json([
            'message' => 'Session ended successfully',
            'marked_absent' => $markedAbsentCount,
            'total_present' => count($presentStudentIds),
            'total_enrolled' => count($enrolledStudentIds),
        ]);
    }

    /**
     * Get session status and attendance summary.
     */
    public function status(ClassSession $session)
    {
        $user = Auth::user();
        if (!$user->teacher || $session->teacher_id !== $user->teacher->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $subjectId = $session->course_id;
        $subject = Subject::with('students')->find($subjectId);

        $enrolledCount = $subject ? $subject->students->count() : 0;

        $attendanceStats = AttendanceRecord::where('session_id', $session->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return response()->json([
            'session' => $session,
            'enrolled_count' => $enrolledCount,
            'present_count' => $attendanceStats['present'] ?? 0,
            'late_count' => $attendanceStats['late'] ?? 0,
            'absent_count' => $attendanceStats['absent'] ?? 0,
            'unmarked_count' => $enrolledCount - array_sum($attendanceStats),
        ]);
    }
}
