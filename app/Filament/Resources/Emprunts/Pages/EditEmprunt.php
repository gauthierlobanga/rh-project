<?php

namespace App\Filament\Resources\Emprunts\Pages;

use App\Filament\Resources\Emprunts\EmpruntResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditEmprunt extends EditRecord
{
    protected static string $resource = EmpruntResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
