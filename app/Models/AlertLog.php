<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'alert_config_id',
        'student_id',
        'guardian_id',
        'alert_type',
        'message',
        'data',
        'status',
        'sent_at',
        'acknowledged_at',
    ];

    protected $casts = [
        'data' => 'array',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function alertConfiguration()
    {
        return $this->belongsTo(AlertConfiguration::class, 'alert_config_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }
}
