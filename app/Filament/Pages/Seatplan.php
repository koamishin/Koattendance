<?php

namespace App\Filament\Pages;

use App\Models\AttendanceRecord;
use App\Models\Seating;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class Seatplan extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.seatplan';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-squares-2x2';
    }

    public function table(Table $table): Table
    {
        $today = Carbon::today();

        return $table
            ->query(
                Seating::with(['student', 'student.attendanceRecords' => function ($query) use ($today) {
                    $query->whereDate('date', $today);
                }])
            )
            ->columns([
                TextColumn::make('seat_number')
                    ->label('Seat #')
                    ->sortable(),
                TextColumn::make('student.name')
                    ->label('Student Name')
                    ->placeholder('—')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('student.student_id')
                    ->label('Student ID')
                    ->placeholder('—')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('student.email')
                    ->label('Email')
                    ->placeholder('—')
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function ($record) use ($today) {
                        if (!$record->student) {
                            return null;
                        }
                        $attendance = $record->student->attendanceRecords->first();
                        return $attendance ? $attendance->status : 'absent';
                    })
                    ->colors([
                        'success' => 'present',
                        'warning' => 'late',
                        'danger' => 'absent',
                    ]),
            ])
            ->defaultSort('seat_number')
            ->striped();
    }
}

