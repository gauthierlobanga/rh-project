<?php

namespace App\Filament\Resources\Cotisations\Pages;

use App\Filament\Resources\Cotisations\CotisationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCotisations extends ListRecords
{
    protected static string $resource = CotisationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
