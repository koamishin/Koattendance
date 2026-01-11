<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Attendance extends Page
{
    protected string $view = 'filament.pages.attendance';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-clipboard-document-check';
    }
}
