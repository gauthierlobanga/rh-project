<?php

namespace App\Filament\Resources\Echeances\Pages;

use App\Filament\Resources\Echeances\EcheanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEcheance extends EditRecord
{
    protected static string $resource = EcheanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
