<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'grade_level',
        'credits',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function classSessions()
    {
        return $this->hasMany(ClassSession::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}
