<?php

namespace App\Filament\Pages;

use App\Models\AttendanceRecord;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MarkAttendance extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.mark-attendance';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-clipboard-document-check';
    }

    public $attendanceDate;
    public $attendanceTime;
    public $studentAttendance = [];

    public function mount(): void
    {
        $this->attendanceDate = today()->format('Y-m-d');
        $this->attendanceTime = now()->format('H:i');
        $this->loadStudents();
    }

    public function loadStudents(): void
    {
        $students = Student::orderBy('name')->get();
        
        foreach ($students as $student) {
            $this->studentAttendance[$student->id] = [
                'id' => $student->id,
                'name' => $student->name,
                'status' => 'present',
                'time' => $this->attendanceTime,
            ];
        }
    }

    public function saveAttendance(): void
    {
        $date = Carbon::createFromFormat('Y-m-d', $this->attendanceDate)->toDateString();

        foreach ($this->studentAttendance as $attendance) {
            if (empty($attendance['name'])) {
                continue;
            }

            $time = !empty($attendance['time']) && $attendance['status'] !== 'absent' 
                ? $attendance['time'] 
                : null;

            AttendanceRecord::updateOrCreate(
                [
                    'student_id' => $attendance['id'],
                    'date' => $date,
                ],
                [
                    'student_name' => $attendance['name'],
                    'status' => $attendance['status'],
                    'time' => $time,
                ]
            );
        }

        Notification::make()
            ->success()
            ->title('Attendance Saved')
            ->body('Attendance records have been saved successfully.')
            ->send();
    }
}
