<?php

namespace App\Filament\Resources\ProduitAssurances\Pages;

use App\Filament\Resources\ProduitAssurances\ProduitAssuranceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduitAssurance extends EditRecord
{
    protected static string $resource = ProduitAssuranceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
