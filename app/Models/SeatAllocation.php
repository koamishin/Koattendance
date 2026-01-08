<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'seat_plan_id',
        'student_id',
        'row',
        'column',
        'seat_label',
    ];

    public function seatPlan()
    {
        return $this->belongsTo(SeatPlan::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
