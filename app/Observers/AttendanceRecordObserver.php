<?php

namespace App\Observers;

use App\Models\AttendanceRecord;
use App\Notifications\AttendanceStatusNotification;
use Illuminate\Support\Facades\Notification;

class AttendanceRecordObserver
{
    /**
     * Handle the AttendanceRecord "created" event.
     */
    public function created(AttendanceRecord $attendanceRecord): void
    {
        $this->sendNotification($attendanceRecord);
    }

    /**
     * Handle the AttendanceRecord "updated" event.
     */
    public function updated(AttendanceRecord $attendanceRecord): void
    {
        // Only send if status changed
        if ($attendanceRecord->isDirty('status')) {
            $this->sendNotification($attendanceRecord);
        }
    }

    /**
     * Send notification to guardian and student.
     */
    protected function sendNotification(AttendanceRecord $attendanceRecord): void
    {
        $student = $attendanceRecord->student;
        if (! $student) {
            return;
        }

        $recipients = collect();

        // 1. Add Guardian if they have an email
        if ($student->guardian && $student->guardian->email) {
            $recipients->push($student->guardian);
        }

        // 2. Add Student's User if they have an email
        if ($student->user && $student->user->email) {
            $recipients->push($student->user);
        }

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new AttendanceStatusNotification($attendanceRecord));
        }
    }

    /**
     * Handle the AttendanceRecord "deleted" event.
     */
    public function deleted(AttendanceRecord $attendanceRecord): void
    {
        //
    }

    /**
     * Handle the AttendanceRecord "restored" event.
     */
    public function restored(AttendanceRecord $attendanceRecord): void
    {
        //
    }

    /**
     * Handle the AttendanceRecord "force deleted" event.
     */
    public function forceDeleted(AttendanceRecord $attendanceRecord): void
    {
        //
    }
}
