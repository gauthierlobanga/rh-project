<?php

namespace App\Filament\Resources\ContratAssuranceVies\Pages;

use App\Filament\Resources\ContratAssuranceVies\ContratAssuranceVieResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditContratAssuranceVie extends EditRecord
{
    protected static string $resource = ContratAssuranceVieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
