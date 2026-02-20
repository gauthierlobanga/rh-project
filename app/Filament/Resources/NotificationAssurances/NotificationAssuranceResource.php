<?php

namespace App\Filament\Resources\NotificationAssurances;

use App\Filament\Resources\NotificationAssurances\Pages\CreateNotificationAssurance;
use App\Filament\Resources\NotificationAssurances\Pages\EditNotificationAssurance;
use App\Filament\Resources\NotificationAssurances\Pages\ListNotificationAssurances;
use App\Filament\Resources\NotificationAssurances\Schemas\NotificationAssuranceForm;
use App\Filament\Resources\NotificationAssurances\Tables\NotificationAssurancesTable;
use App\Models\NotificationAssurance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NotificationAssuranceResource extends Resource
{
    protected static ?string $model = NotificationAssurance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return NotificationAssuranceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationAssurancesTable::configure($table);
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
            'index' => ListNotificationAssurances::route('/'),
            'create' => CreateNotificationAssurance::route('/create'),
            'edit' => EditNotificationAssurance::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('est_lue', false)->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
