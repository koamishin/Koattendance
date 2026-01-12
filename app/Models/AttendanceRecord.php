<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'subject_id',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'device_info' => 'array',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function scanEvent(): BelongsTo
    {
        return $this->belongsTo(QrScanEvent::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
