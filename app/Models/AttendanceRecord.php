<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $fillable = ['student_id', 'student_name', 'status', 'time', 'date'];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime:H:i',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
