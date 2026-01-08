<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'student_id',
        'status',
        'timestamp',
        'recorded_by',
        'scan_event_id',
        'device_info',
        'notes',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'device_info' => 'array',
    ];

    public function session()
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scanEvent()
    {
        return $this->belongsTo(QrScanEvent::class);
    }
}
