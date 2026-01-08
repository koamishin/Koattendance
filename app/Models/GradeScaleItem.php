<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeScaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_scale_id',
        'letter_grade',
        'min_percentage',
        'max_percentage',
        'gpa_points',
        'description',
    ];

    protected $casts = [
        'min_percentage' => 'decimal:2',
        'max_percentage' => 'decimal:2',
        'gpa_points' => 'decimal:2',
    ];

    public function gradeScale()
    {
        return $this->belongsTo(GradeScale::class);
    }
}
