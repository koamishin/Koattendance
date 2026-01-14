<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'section_id',
        'teacher_id',
        'room',
        'scheduled_date',
        'start_time',
        'end_time',
        'status',
        'attendance_mode',
        'notes',
        'late_threshold_minutes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'late_threshold_minutes' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function qrScanEvents()
    {
        return $this->hasMany(QrScanEvent::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}
