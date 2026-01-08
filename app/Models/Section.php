<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grade_level',
        'academic_year',
        'semester',
        'advisor_id',
        'max_students',
        'schedule_template',
    ];

    protected $casts = [
        'schedule_template' => 'array',
    ];

    public function advisor()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class);
    }

    public function seatPlans()
    {
        return $this->hasMany(SeatPlan::class);
    }
}
