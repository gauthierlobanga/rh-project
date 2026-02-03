<?php

namespace App\Filament\Resources\ContratAssuranceVies\Pages;

use App\Filament\Resources\ContratAssuranceVies\ContratAssuranceVieResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContratAssuranceVies extends ListRecords
{
    protected static string $resource = ContratAssuranceVieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
