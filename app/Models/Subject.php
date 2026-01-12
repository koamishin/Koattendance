<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'teacher_id', 'icon'];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function seatPlans(): HasMany
    {
        return $this->hasMany(SeatPlan::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_subject');
    }
}
