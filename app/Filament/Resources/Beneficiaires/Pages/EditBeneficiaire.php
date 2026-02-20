<?php

namespace App\Filament\Resources\Beneficiaires\Pages;

use App\Filament\Resources\Beneficiaires\BeneficiaireResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBeneficiaire extends EditRecord
{
    protected static string $resource = BeneficiaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
