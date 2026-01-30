<?php

namespace App\Filament\Resources\Emprunts;

use App\Filament\Resources\Emprunts\Pages\CreateEmprunt;
use App\Filament\Resources\Emprunts\Pages\EditEmprunt;
use App\Filament\Resources\Emprunts\Pages\ListEmprunts;
use App\Filament\Resources\Emprunts\Schemas\EmpruntForm;
use App\Filament\Resources\Emprunts\Tables\EmpruntsTable;
use App\Models\Emprunt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmpruntResource extends Resource
{
    protected static ?string $model = Emprunt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'montant_emprunt';

    public static function form(Schema $schema): Schema
    {
        return EmpruntForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmpruntsTable::configure($table);
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
            'index' => ListEmprunts::route('/'),
            'create' => CreateEmprunt::route('/create'),
            'edit' => EditEmprunt::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
