<?php

namespace App\Filament\Resources\ProduitAssurances;

use App\Filament\Resources\ProduitAssurances\Pages\CreateProduitAssurance;
use App\Filament\Resources\ProduitAssurances\Pages\EditProduitAssurance;
use App\Filament\Resources\ProduitAssurances\Pages\ListProduitAssurances;
use App\Filament\Resources\ProduitAssurances\Schemas\ProduitAssuranceForm;
use App\Filament\Resources\ProduitAssurances\Tables\ProduitAssurancesTable;
use App\Models\ProduitAssurance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ProduitAssuranceResource extends Resource
{
    protected static ?string $model = ProduitAssurance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Assurance';

    protected static ?string $recordTitleAttribute = 'nom_produit';

    public static function form(Schema $schema): Schema
    {
        return ProduitAssuranceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProduitAssurancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProduitAssurances::route('/'),
            'create' => CreateProduitAssurance::route('/create'),
            'edit' => EditProduitAssurance::route('/{record}/edit'),
        ];
    }
}
