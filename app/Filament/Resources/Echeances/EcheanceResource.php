<?php

namespace App\Filament\Resources\Echeances;

use App\Filament\Resources\Echeances\Pages\CreateEcheance;
use App\Filament\Resources\Echeances\Pages\EditEcheance;
use App\Filament\Resources\Echeances\Pages\ListEcheances;
use App\Filament\Resources\Echeances\Schemas\EcheanceForm;
use App\Filament\Resources\Echeances\Tables\EcheancesTable;
use App\Models\Echeance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EcheanceResource extends Resource
{
    protected static ?string $model = Echeance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'numero_echeance';

    public static function form(Schema $schema): Schema
    {
        return EcheanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EcheancesTable::configure($table);
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
            'index' => ListEcheances::route('/'),
            'create' => CreateEcheance::route('/create'),
            'edit' => EditEcheance::route('/{record}/edit'),
        ];
    }
}
