<?php

namespace App\Filament\Resources\Cotisations;

use App\Filament\Resources\Cotisations\Pages\CreateCotisation;
use App\Filament\Resources\Cotisations\Pages\EditCotisation;
use App\Filament\Resources\Cotisations\Pages\ListCotisations;
use App\Filament\Resources\Cotisations\Schemas\CotisationForm;
use App\Filament\Resources\Cotisations\Tables\CotisationsTable;
use App\Models\Cotisation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CotisationResource extends Resource
{
    protected static ?string $model = Cotisation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Assurance';

    protected static ?string $recordTitleAttribute = 'contrat_id';

    public static function form(Schema $schema): Schema
    {
        return CotisationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CotisationsTable::configure($table);
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
            'index' => ListCotisations::route('/'),
            'create' => CreateCotisation::route('/create'),
            'edit' => EditCotisation::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('statut_paiement', 'en_retard')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }
}
