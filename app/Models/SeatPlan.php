<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeatPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'subject_id',
        'name',
        'layout',
    ];

    protected $casts = [
        'layout' => 'array',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function seatAllocations()
    {
        return $this->hasMany(SeatAllocation::class);
    }
}
