<?php

namespace App\Filament\Resources\Beneficiaires\Pages;

use App\Filament\Resources\Beneficiaires\BeneficiaireResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBeneficiaires extends ListRecords
{
    protected static string $resource = BeneficiaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
