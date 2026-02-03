<?php

namespace App\Filament\Resources\Cotisations\Pages;

use App\Filament\Resources\Cotisations\CotisationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCotisation extends EditRecord
{
    protected static string $resource = CotisationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
