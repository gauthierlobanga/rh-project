<?php

namespace App\Filament\Resources\Clients;

use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Clients\Schemas\ClientForm;
use App\Filament\Resources\Clients\Tables\ClientsTable;
use App\Models\Client;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Assurance';

    protected static ?string $recordTitleAttribute = 'user_id';

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //   RelationManagers\ContratsRelationManager::class,
            //     RelationManagers\BeneficiairesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    //   public static function infolist(Infolist $infolist): Infolist
    // {
    //     return $infolist
    //         ->schema([
    //             Infolists\Components\Section::make('Identité')
    //                 ->schema([
    //                     Infolists\Components\Grid::make(4)
    //                         ->schema([
    //                             Infolists\Components\TextEntry::make('nom_complet')
    //                                 ->icon('heroicon-o-user'),

    //                             Infolists\Components\TextEntry::make('type_client')
    //                                 ->badge()
    //                                 ->icon('heroicon-o-tag'),

    //                             Infolists\Components\TextEntry::make('date_naissance')
    //                                 ->date()
    //                                 ->icon('heroicon-o-cake'),

    //                             Infolists\Components\TextEntry::make('age')
    //                                 ->label('Âge')
    //                                 ->icon('heroicon-o-clock'),
    //                         ]),

    //                     Infolists\Components\Grid::make(3)
    //                         ->schema([
    //                             Infolists\Components\TextEntry::make('situation_familiale')
    //                                 ->icon('heroicon-o-heart'),

    //                             Infolists\Components\TextEntry::make('nombre_enfants')
    //                                 ->icon('heroicon-o-user-group'),

    //                             Infolists\Components\TextEntry::make('profession')
    //                                 ->icon('heroicon-o-briefcase'),
    //                         ]),
    //                 ]),

    //             Infolists\Components\Section::make('Coordonnées')
    //                 ->schema([
    //                     Infolists\Components\Grid::make(3)
    //                         ->schema([
    //                             Infolists\Components\TextEntry::make('email')
    //                                 ->icon('heroicon-o-envelope'),

    //                             Infolists\Components\TextEntry::make('telephone_mobile')
    //                                 ->icon('heroicon-o-phone'),

    //                             Infolists\Components\TextEntry::make('telephone_fixe')
    //                                 ->icon('heroicon-o-phone'),
    //                         ]),

    //                     Infolists\Components\TextEntry::make('adresse_complete')
    //                         ->label('Adresse')
    //                         ->icon('heroicon-o-map-pin')
    //                         ->columnSpanFull(),
    //                 ]),

    //             Infolists\Components\Section::make('Documents')
    //                 ->schema([
    //                     Infolists\Components\Grid::make(2)
    //                         ->schema([
    //                             Infolists\Components\TextEntry::make('numero_cni')
    //                                 ->icon('heroicon-o-identification'),

    //                             Infolists\Components\IconEntry::make('kyc_verifie')
    //                                 ->label('KYC Vérifié')
    //                                 ->boolean()
    //                                 ->trueIcon('heroicon-o-check-circle')
    //                                 ->falseIcon('heroicon-o-x-circle'),
    //                         ]),
    //                 ]),

    //             Infolists\Components\Section::make('Statistiques')
    //                 ->schema([
    //                     Infolists\Components\Grid::make(3)
    //                         ->schema([
    //                             Infolists\Components\TextEntry::make('contrats_count')
    //                                 ->label('Nombre de contrats')
    //                                 ->icon('heroicon-o-document-text'),

    //                             Infolists\Components\TextEntry::make('total_capital_assure')
    //                                 ->label('Capital total assuré')
    //                                 ->money('EUR')
    //                                 ->icon('heroicon-o-currency-euro'),

    //                             Infolists\Components\TextEntry::make('total_prime_annuelle')
    //                                 ->label('Prime annuelle totale')
    //                                 ->money('EUR')
    //                                 ->icon('heroicon-o-banknotes'),
    //                         ]),
    //                 ]),
    //         ]);
    // }
}
