<?php

namespace App\Filament\Resources\Agents;

use App\Filament\Resources\Agents\Pages\CreateAgent;
use App\Filament\Resources\Agents\Pages\EditAgent;
use App\Filament\Resources\Agents\Pages\ListAgents;
use App\Filament\Resources\Agents\Schemas\AgentForm;
use App\Filament\Resources\Agents\Tables\AgentsTable;
use App\Models\Agent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AgentResource extends Resource
{
    protected static ?string $model = Agent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Personnel';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'utilisateur.name';

    public static function form(Schema $schema): Schema
    {
        return AgentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\ClientsRelationManager::class,
            // RelationManagers\ContratsRelationManager::class,
            // RelationManagers\CommissionsRelationManager::class,
            // RelationManagers\SinistresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgents::route('/'),
            'create' => CreateAgent::route('/create'),
            'edit' => EditAgent::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('statut_agent', 'actif')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    /*
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations professionnelles')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('matricule_agent')
                                    ->icon('heroicon-o-identification'),

                                Infolists\Components\TextEntry::make('nom_complet')
                                    ->icon('heroicon-o-user'),

                                Infolists\Components\TextEntry::make('statut_agent')
                                    ->badge()
                                    ->icon('heroicon-o-status-online'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('numero_agrement')
                                    ->icon('heroicon-o-shield-check'),

                                Infolists\Components\TextEntry::make('date_expiration_agrement')
                                    ->date()
                                    ->icon('heroicon-o-calendar')
                                    ->color(fn ($record) =>
                                        $record->date_expiration_agrement &&
                                        $record->date_expiration_agrement->isPast() ?
                                        'danger' : null
                                    ),

                                Infolists\Components\IconEntry::make('agrement_valide')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle'),
                            ]),

                        Infolists\Components\TextEntry::make('agence_affectation')
                            ->icon('heroicon-o-building-office'),
                    ]),

                Infolists\Components\Section::make('Performance')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('nombre_contrats')
                                    ->icon('heroicon-o-document-text'),

                                Infolists\Components\TextEntry::make('valeur_portefeuille')
                                    ->money('EUR')
                                    ->icon('heroicon-o-currency-euro'),

                                Infolists\Components\TextEntry::make('taux_conversion')
                                    ->suffix('%')
                                    ->icon('heroicon-o-arrow-trending-up'),

                                Infolists\Components\TextEntry::make('total_commissions')
                                    ->money('EUR')
                                    ->icon('heroicon-o-currency-dollar'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('objectif_annuel')
                                    ->money('EUR')
                                    ->icon('heroicon-o-flag'),

                                Infolists\Components\TextEntry::make('taux_commission')
                                    ->suffix('%')
                                    ->icon('heroicon-o-percent-badge'),

                                Infolists\Components\TextEntry::make('performance_annuelle')
                                    ->suffix('%')
                                    ->icon('heroicon-o-chart-bar')
                                    ->color(fn ($record) =>
                                        $record->performance_annuelle >= 100 ? 'success' :
                                        ($record->performance_annuelle >= 80 ? 'warning' : 'danger')
                                    ),
                            ]),
                    ]),

                Infolists\Components\Section::make('Contacts')
                    ->schema([
                        Infolists\Components\TextEntry::make('email')
                            ->icon('heroicon-o-envelope'),

                        Infolists\Components\TextEntry::make('telephone')
                            ->icon('heroicon-o-phone'),
                    ]),
            ]);
    }

 */
}
