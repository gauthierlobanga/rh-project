<?php

namespace App\Filament\Resources\ContratAssuranceVies;

use App\Filament\Resources\ContratAssuranceVies\Pages\CreateContratAssuranceVie;
use App\Filament\Resources\ContratAssuranceVies\Pages\EditContratAssuranceVie;
use App\Filament\Resources\ContratAssuranceVies\Pages\ListContratAssuranceVies;
use App\Filament\Resources\ContratAssuranceVies\Schemas\ContratAssuranceVieForm;
use App\Filament\Resources\ContratAssuranceVies\Tables\ContratAssuranceViesTable;
use App\Models\ContratAssuranceVie;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ContratAssuranceVieResource extends Resource
{
    protected static ?string $model = ContratAssuranceVie::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Assurance';

    protected static ?string $recordTitleAttribute = 'numero_contrat';

    public static function form(Schema $schema): Schema
    {
        return ContratAssuranceVieForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContratAssuranceViesTable::configure($table);
    }

    //  public static function infolist(Infolist $infolist): Infolist
    // {
    //     return $infolist
    //         ->schema([
    //             Infolists\Components\Section::make('Informations du contrat')
    //                 ->schema([
    //                     Infolists\Components\Grid::make(3)
    //                         ->schema([
    //                             Infolists\Components\TextEntry::make('numero_contrat')
    //                                 ->icon('heroicon-o-hashtag'),

    //                             Infolists\Components\TextEntry::make('statut_contrat')
    //                                 ->badge()
    //                                 ->color(fn (string $state): string => match ($state) {
    //                                     'actif' => 'success',
    //                                     'en_attente' => 'warning',
    //                                     'suspendu' => 'danger',
    //                                     'resilie' => 'gray',
    //                                     'expire' => 'gray',
    //                                     default => 'gray',
    //                                 }),

    //                             Infolists\Components\TextEntry::make('numero_police')
    //                                 ->icon('heroicon-o-shield-check'),
    //                         ]),

    //                     Infolists\Components\Grid::make(3)
    //                         ->schema([
    //                             Infolists\Components\TextEntry::make('souscripteur.nom_complet')
    //                                 ->icon('heroicon-o-user'),

    //                             Infolists\Components\TextEntry::make('produit.nom_produit')
    //                                 ->icon('heroicon-o-cube'),

    //                             Infolists\Components\TextEntry::make('agent.nom_complet')
    //                                 ->icon('heroicon-o-user-circle'),
    //                         ]),
    //                 ]),

    //             Infolists\Components\Section::make('Dates importantes')
    //                 ->schema([
    //                     Infolists\Components\Grid::make(4)
    //                         ->schema([
    //                             Infolists\Components\TextEntry::make('date_effet')
    //                                 ->date()
    //                                 ->icon('heroicon-o-calendar'),

    //                             Infolists\Components\TextEntry::make('date_echeance')
    //                                 ->date()
    //                                 ->icon('heroicon-o-calendar-days'),

    //                             Infolists\Components\TextEntry::make('date_signature')
    //                                 ->date()
    //                                 ->icon('heroicon-o-pencil'),

    //                             Infolists\Components\TextEntry::make('date_validation')
    //                                 ->date()
    //                                 ->icon('heroicon-o-check'),
    //                         ]),
    //                 ]),

    //             Infolists\Components\Section::make('Informations financières')
    //                 ->schema([
    //                     Infolists\Components\Grid::make(3)
    //                         ->schema([
    //                             Infolists\Components\TextEntry::make('capital_assure')
    //                                 ->money('EUR')
    //                                 ->icon('heroicon-o-currency-euro'),

    //                             Infolists\Components\TextEntry::make('prime_annuelle')
    //                                 ->money('EUR')
    //                                 ->icon('heroicon-o-banknotes'),

    //                             Infolists\Components\TextEntry::make('montant_periodicite')
    //                                 ->money('EUR')
    //                                 ->icon('heroicon-o-clock'),
    //                         ]),

    //                     Infolists\Components\Grid::make(4)
    //                         ->schema([
    //                             Infolists\Components\TextEntry::make('frequence_paiement')
    //                                 ->badge()
    //                                 ->icon('heroicon-o-clock'),

    //                             Infolists\Components\TextEntry::make('frais_gestion')
    //                                 ->suffix('%')
    //                                 ->icon('heroicon-o-cog'),

    //                             Infolists\Components\TextEntry::make('frais_entree')
    //                                 ->suffix('%')
    //                                 ->icon('heroicon-o-arrow-right-circle'),

    //                             Infolists\Components\TextEntry::make('frais_sortie')
    //                                 ->suffix('%')
    //                                 ->icon('heroicon-o-arrow-left-circle'),
    //                         ]),
    //                 ]),

    //             Infolists\Components\Section::make('Valeurs actuelles')
    //                 ->schema([
    //                     Infolists\Components\Grid::make(3)
    //                         ->schema([
    //                             Infolists\Components\TextEntry::make('valeur_rachat')
    //                                 ->money('EUR')
    //                                 ->icon('heroicon-o-arrow-left-circle'),

    //                             Infolists\Components\TextEntry::make('valeur_epargne')
    //                                 ->money('EUR')
    //                                 ->icon('heroicon-o-chart-bar'),

    //                             Infolists\Components\TextEntry::make('age_contrat')
    //                                 ->label('Âge du contrat')
    //                                 ->suffix(' ans')
    //                                 ->icon('heroicon-o-clock'),
    //                         ]),
    //                 ]),

    //             Infolists\Components\Section::make('Statistiques')
    //                 ->schema([
    //                     Infolists\Components\Grid::make(2)
    //                         ->schema([
    //                             Infolists\Components\TextEntry::make('cotisations_count')
    //                                 ->label('Nombre de cotisations')
    //                                 ->icon('heroicon-o-credit-card'),

    //                             Infolists\Components\TextEntry::make('beneficiaires_count')
    //                                 ->label('Nombre de bénéficiaires')
    //                                 ->icon('heroicon-o-users'),
    //                         ]),
    //                 ]),
    //         ]);
    // }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\BeneficiairesRelationManager::class,
            // RelationManagers\CotisationsRelationManager::class,
            // RelationManagers\SinistresRelationManager::class,
            // RelationManagers\CommissionsRelationManager::class,
            // RelationManagers\PaiementsRelationManager::class,
            // RelationManagers\ReservesRelationManager::class,
            // RelationManagers\HistoriqueRelationManager::class,
            // RelationManagers\AchatsRachatsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContratAssuranceVies::route('/'),
            'create' => CreateContratAssuranceVie::route('/create'),
            'edit' => EditContratAssuranceVie::route('/{record}/edit'),
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
        return static::getModel()::where('statut_contrat', 'actif')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}
