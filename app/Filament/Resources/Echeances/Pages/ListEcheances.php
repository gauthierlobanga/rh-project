<?php

namespace App\Filament\Resources\Echeances\Pages;

use App\Filament\Resources\Echeances\EcheanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEcheances extends ListRecords
{
    protected static string $resource = EcheanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
