<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_id',
        'first_name',
        'last_name',
        'middle_name',
        'birth_date',
        'gender',
        'address',
        'phone',
        'guardian_id',
        'current_grade_level',
        'section_id',
        'status',
        'enrollment_date',
        'qr_code_data',
        'qr_code_active',
        'qr_code_regenerated_at',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'enrollment_date' => 'date',
        'qr_code_active' => 'boolean',
        'qr_code_regenerated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'student_subject');
    }

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function qrScanEvents(): HasMany
    {
        return $this->hasMany(QrScanEvent::class);
    }

    /**
     * Generate a new QR code for the student.
     */
    public function generateQrCode(): string
    {
        $data = [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'generated_at' => now()->timestamp,
            'nonce' => \Illuminate\Support\Str::random(16),
        ];

        // Encrypt the data
        $encrypted = \Illuminate\Support\Facades\Crypt::encrypt($data);
        
        $this->update([
            'qr_code_data' => $encrypted,
            'qr_code_active' => true,
            'qr_code_regenerated_at' => now(),
        ]);

        return $encrypted;
    }
}
