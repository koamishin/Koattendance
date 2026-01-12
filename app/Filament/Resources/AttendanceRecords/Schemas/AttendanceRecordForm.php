<?php

namespace App\Filament\Resources\AttendanceRecords\Schemas;

use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class AttendanceRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('student_name')
                    ->label('Student')
                    ->options(Student::query()->pluck('name', 'name'))
                    ->required()
                    ->searchable(),
                Select::make('status')
                    ->options([
                        'present' => 'Present',
                        'absent' => 'Absent',
                        'late' => 'Late',
                    ])
                    ->required(),
                TimePicker::make('time')
                    ->seconds(false),
                DatePicker::make('date')
                    ->required(),
            ]);
    }
}
