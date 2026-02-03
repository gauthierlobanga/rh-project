<?php

namespace App\Filament\Resources\ProduitAssurances\Pages;

use App\Filament\Resources\ProduitAssurances\ProduitAssuranceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProduitAssurances extends ListRecords
{
    protected static string $resource = ProduitAssuranceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
