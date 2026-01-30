<?php

namespace App\Filament\Resources\Emprunts\Pages;

use App\Filament\Resources\Emprunts\EmpruntResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmprunts extends ListRecords
{
    protected static string $resource = EmpruntResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
