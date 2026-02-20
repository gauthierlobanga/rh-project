<?php

namespace App\Filament\Resources\Sinistres\Pages;

use App\Filament\Resources\Sinistres\SinistreResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSinistre extends EditRecord
{
    protected static string $resource = SinistreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
