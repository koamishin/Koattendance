<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ClassSession;
use App\Models\QrScanEvent;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AttendanceScanController extends Controller
{
    /**
     * Scan a student's QR code for a specific class session.
     */
    public function scan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'session_id' => 'required|exists:class_sessions,id',
            'location' => 'nullable|array',
            'device_info' => 'nullable|array',
        ]);

        $session = ClassSession::findOrFail($request->session_id);

        try {
            $data = Crypt::decrypt($request->qr_code);
        } catch (\Exception $e) {
            return $this->recordFailedScan($session, null, 'Invalid QR Code', $request);
        }

        $studentId = $data['id'] ?? null;
        if (!$studentId) {
             return $this->recordFailedScan($session, null, 'Malformed QR Data', $request);
        }

        $student = Student::find($studentId);

        if (!$student) {
            return $this->recordFailedScan($session, null, 'Student not found', $request);
        }

        if (!$student->qr_code_active) {
            return $this->recordFailedScan($session, $student, 'QR Code is inactive', $request);
        }
        
        // Verify encrypted data matches student (prevent using old QR for different student if IDs reused)
        if ($student->student_id !== ($data['student_id'] ?? null)) {
             return $this->recordFailedScan($session, $student, 'QR Data mismatch', $request);
        }

        // Check if already scanned for this session
        $existingRecord = AttendanceRecord::where('session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingRecord) {
             return $this->recordFailedScan($session, $student, 'Duplicate scan', $request);
        }

        return DB::transaction(function () use ($session, $student, $request) {
            // Create Scan Event
            $scanEvent = QrScanEvent::create([
                'session_id' => $session->id,
                'teacher_id' => $session->teacher_id,
                'student_id' => $student->id,
                'scanned_at' => now(),
                'device_info' => $request->device_info,
                'location' => $request->location,
                'status' => 'success',
            ]);

            // Create Attendance Record
            // Note: AttendanceRecord expects 'subject_id' but ClassSession has 'course_id'.
            // If they are not compatible, we leave subject_id null.
            $attendance = AttendanceRecord::create([
                'session_id' => $session->id,
                'student_id' => $student->id,
                'status' => 'present',
                'timestamp' => now(),
                'recorded_by' => Auth::id(), 
                'scan_event_id' => $scanEvent->id,
                'device_info' => $request->device_info,
                // 'subject_id' => $session->course_id, // Uncomment if course_id maps to subject_id
            ]);

            return response()->json([
                'message' => 'Attendance recorded successfully',
                'student' => [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'student_id' => $student->student_id,
                ],
                'scan_status' => 'success',
            ]);
        });
    }

    private function recordFailedScan(ClassSession $session, ?Student $student, string $error, Request $request)
    {
        QrScanEvent::create([
            'session_id' => $session->id,
            'teacher_id' => $session->teacher_id,
            'student_id' => $student?->id,
            'scanned_at' => now(),
            'device_info' => $request->device_info,
            'location' => $request->location,
            'status' => 'failed',
            'error_message' => $error,
        ]);

        return response()->json([
            'message' => $error,
            'scan_status' => 'failed',
        ], 400);
    }
}
