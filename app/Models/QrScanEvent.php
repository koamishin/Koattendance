<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrScanEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'teacher_id',
        'student_id',
        'scanned_at',
        'device_info',
        'location',
        'status',
        'error_message',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'device_info' => 'array',
        'location' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function attendanceRecord()
    {
        return $this->hasOne(AttendanceRecord::class);
    }
}
