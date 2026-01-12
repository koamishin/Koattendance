<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassSessionController extends Controller
{
    /**
     * Get or create a session for attendance.
     * simplified for "Start Session" flow.
     */
    public function start(Request $request)
    {
        $request->validate([
            'course_id' => 'nullable|exists:courses,id', // or subject_id
            'section_id' => 'nullable|exists:sections,id',
            'subject_id' => 'nullable|exists:subjects,id', // if using subjects table
        ]);
        
        // This is a simplified logic. In a real app, you might pick from a schedule.
        // For now, we create a session for "Now".
        
        $user = Auth::user();
        if (!$user->teacher) {
            return response()->json(['message' => 'Only teachers can start sessions'], 403);
        }

        // For this demo, we assume we are creating/finding a session for the selected subject
        // We'll map "subject" to "course" for now, or just use course_id if frontend sends it.
        // The AttendanceController uses "Subject" model. ClassSession uses "Course". 
        // We should align them. But to keep it working with existing AttendanceController:
        
        // If the frontend sends 'subject_id' (from the dropdown), we treat it as the course for the session.
        // We might need to find a dummy section or create one if not provided.
        
        $subjectId = $request->input('subject_id');
        
        // Find existing session for today for this teacher and subject
        $session = ClassSession::where('teacher_id', $user->teacher->id)
            ->whereDate('scheduled_date', today())
            // ->where('course_id', $subjectId) // Assuming subject_id maps to course_id
            ->first();

        if (!$session) {
             // Create a new session
             // We need a section_id. Let's pick the first one or require it.
             // For simplicity, let's create a "General" session if no section is picked.
             // Or better, let's just create one.
             
             // We need a valid course_id and section_id because of foreign keys.
             // This might be tricky without full data.
             // Let's assume the user selects a subject (which acts as course).
             // And we pick the first section for that course?
             
             // Hack: Find a course that matches the subject name or ID?
             // Let's just create a session.
             
             // Requires: course_id, section_id.
             // If the system is strictly relational, we need those.
             
             // Let's return a list of "Available Classes" for the teacher to pick from instead?
             // Or just mock it for now if data is missing.
             
             // Let's assume the frontend passes valid IDs.
             
             return response()->json(['message' => 'Please select a valid class from your schedule (Not implemented yet)'], 400);
        }

        return response()->json([
            'session' => $session,
        ]);
    }
    
    // Alternative: List teacher's sessions for today
    public function today() {
        $user = Auth::user();
        if (!$user->teacher) {
            return response()->json([]);
        }
        
        $sessions = ClassSession::with(['course', 'section'])
            ->where('teacher_id', $user->teacher->id)
            ->whereDate('scheduled_date', today())
            ->get();
            
        return response()->json(['sessions' => $sessions]);
    }
    
    // Create a new ad-hoc session
    public function store(Request $request) {
        $user = Auth::user();
         if (!$user->teacher) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Validation...
        
        $session = ClassSession::create([
            'teacher_id' => $user->teacher->id,
            'course_id' => $request->course_id,
            'section_id' => $request->section_id,
            'scheduled_date' => today(),
            'start_time' => now(),
            'end_time' => now()->addHour(),
            'status' => 'in_progress',
        ]);
        
        return response()->json(['session' => $session]);
    }
}
