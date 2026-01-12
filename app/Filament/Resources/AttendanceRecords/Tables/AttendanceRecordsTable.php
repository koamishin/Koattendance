<?php

namespace App\Filament\Resources\AttendanceRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Models\Subject;

class AttendanceRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('subject.name')
                    ->label('Subject')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'absent' => 'danger',
                        'late' => 'warning',
                        'unmarked' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('time')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('date')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('subject_id')
                    ->label('Subject')
                    ->options(Subject::all()->pluck('name', 'id')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
