<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Seatplan extends Page
{
    protected string $view = 'filament.pages.seatplan';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-squares-2x2';
    }
}
