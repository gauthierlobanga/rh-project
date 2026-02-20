<?php

namespace App\Filament\Resources\Beneficiaires;

use App\Filament\Resources\Beneficiaires\Pages\CreateBeneficiaire;
use App\Filament\Resources\Beneficiaires\Pages\EditBeneficiaire;
use App\Filament\Resources\Beneficiaires\Pages\ListBeneficiaires;
use App\Filament\Resources\Beneficiaires\Schemas\BeneficiaireForm;
use App\Filament\Resources\Beneficiaires\Tables\BeneficiairesTable;
use App\Models\Beneficiaire;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BeneficiaireResource extends Resource
{
    protected static ?string $model = Beneficiaire::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nom';

    public static function form(Schema $schema): Schema
    {
        return BeneficiaireForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BeneficiairesTable::configure($table);
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
            'index' => ListBeneficiaires::route('/'),
            'create' => CreateBeneficiaire::route('/create'),
            'edit' => EditBeneficiaire::route('/{record}/edit'),
        ];
    }
}
