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
        if ($session->status !== 'in_progress') {
            return response()->json([
                'message' => 'Session is not active',
                'scan_status' => 'failed',
            ], 400);
        }

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

        $existingRecord = AttendanceRecord::where('session_id', $session->id)
            ->where('student_id', $student->id)
            ->first();

        return DB::transaction(function () use ($session, $student, $request, $existingRecord) {
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

            // Determine if student is late based on threshold
            $status = 'present';
            $lateThreshold = $session->late_threshold_minutes ?? 15; // Default 15 minutes
            
            if ($session->start_time) {
                // Since start_time is cast to datetime, format it to get just the time string
                $startTimeString = $session->start_time->format('H:i:s');
                $sessionStart = \Carbon\Carbon::parse($session->scheduled_date->format('Y-m-d') . ' ' . $startTimeString);
                $lateTime = $sessionStart->copy()->addMinutes($lateThreshold);
                
                if (now()->gt($lateTime)) {
                    $status = 'late';
                }
            }

            // Create Attendance Record
            $attendance = AttendanceRecord::updateOrCreate(
                [
                    'session_id' => $session->id,
                    'student_id' => $student->id,
                ],
                [
                    'subject_id' => $session->subject_id,
                    'status' => $status,
                    'timestamp' => now(),
                    'recorded_by' => Auth::id(),
                    'scan_event_id' => $scanEvent->id,
                    'device_info' => $request->device_info,
                    'notes' => $existingRecord ? 'Updated via QR scan' : null,
                ],
            );

            return response()->json([
                'message' => $existingRecord
                    ? 'Attendance updated successfully'
                    : ($status === 'late' ? 'Marked as late' : 'Attendance recorded successfully'),
                'student' => [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'student_id' => $student->student_id,
                ],
                'status' => $status,
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
