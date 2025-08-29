<?php

namespace App\Filament\ESuratuser\Resources\LetterRequestResource\Pages;

use App\Filament\ESuratuser\Resources\LetterRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLetterRequests extends ManageRecords
{
    protected static string $resource = LetterRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
