<?php

namespace App\Filament\Resources\Sinistres;

use App\Filament\Resources\Sinistres\Pages\CreateSinistre;
use App\Filament\Resources\Sinistres\Pages\EditSinistre;
use App\Filament\Resources\Sinistres\Pages\ListSinistres;
use App\Filament\Resources\Sinistres\Schemas\SinistreForm;
use App\Filament\Resources\Sinistres\Tables\SinistresTable;
use App\Models\Sinistre;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SinistreResource extends Resource
{
    protected static ?string $model = Sinistre::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'numero_sinistre';

    public static function form(Schema $schema): Schema
    {
        return SinistreForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SinistresTable::configure($table);
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
            'index' => ListSinistres::route('/'),
            'create' => CreateSinistre::route('/create'),
            'edit' => EditSinistre::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereIn('statut_sinistre', [
            'declare', 'en_cours_examen', 'documents_manquants', 'expertise_en_cours'
        ])->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
