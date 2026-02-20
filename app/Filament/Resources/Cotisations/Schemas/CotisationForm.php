<?php

namespace App\Filament\Resources\Cotisations\Schemas;

use App\Models\ContratAssuranceVie;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CotisationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de la cotisation')
                    ->schema([
                        Select::make('contrat_id')
                            ->relationship('contrat', 'numero_contrat')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $contrat = ContratAssuranceVie::find($state);
                                    if ($contrat) {
                                        $set('montant_due', $contrat->montant_periodicite);
                                    }
                                }
                            }),

                        Grid::make(3)
                            ->schema([
                                DatePicker::make('date_echeance')
                                    ->required()
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-calendar'),

                                TextInput::make('montant_due')
                                    ->required()
                                    ->numeric()
                                    ->prefix('€')
                                    ->prefixIcon('heroicon-o-currency-euro'),

                                Select::make('statut_paiement')
                                    ->options([
                                        'en_attente' => 'En attente',
                                        'paye' => 'Payé',
                                        'en_retard' => 'En retard',
                                        'partiellement_paye' => 'Partiellement payé',
                                        'annule' => 'Annulé',
                                    ])
                                    ->required()
                                    ->prefixIcon('heroicon-o-status-online'),
                            ]),
                    ]),

                Section::make('Paiement')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                DatePicker::make('date_paiement')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-calendar'),

                                TextInput::make('montant_paye')
                                    ->numeric()
                                    ->prefix('€')
                                    ->prefixIcon('heroicon-o-currency-euro'),

                                Select::make('mode_paiement')
                                    ->options([
                                        'prelevement' => 'Prélèvement',
                                        'virement' => 'Virement',
                                        'cheque' => 'Chèque',
                                        'especes' => 'Espèces',
                                        'carte' => 'Carte bancaire',
                                    ])
                                    ->prefixIcon('heroicon-o-credit-card'),
                            ]),

                        Grid::make(columns: 2)
                            ->schema([
                                TextInput::make('penalite_retard')
                                    ->numeric()
                                    ->prefix('€')
                                    ->prefixIcon('heroicon-o-exclamation-triangle'),

                                TextInput::make('interets_moratoires')
                                    ->numeric()
                                    ->prefix('€')
                                    ->prefixIcon('heroicon-o-clock'),
                            ]),

                        TextInput::make('reference_paiement')
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-hashtag'),

                        DatePicker::make('date_encaissement')
                            ->native(false)
                            ->prefixIcon('heroicon-o-calendar'),
                    ]),

                Section::make('Gestion des relances')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('est_rappele')
                                    ->label('Relance effectuée')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-bell-alert')
                                    ->offIcon('heroicon-o-bell'),

                                DatePicker::make('date_rappel')
                                    ->native(false)
                                    ->disabled(fn ($get) => ! $get('est_rappele'))
                                    ->prefixIcon('heroicon-o-calendar'),

                                TextInput::make('nombre_relances')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefixIcon('heroicon-o-phone'),
                            ]),
                    ]),

                Section::make('Informations complémentaires')
                    ->schema([
                        TextInput::make('numero_facture')
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-document'),

                        KeyValue::make('details_paiement'),

                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
/*
 <?php

namespace App\Filament\Resources;

use App\Filament\Resources\CotisationResource\Pages;
use App\Filament\Resources\CotisationResource\RelationManagers;
use App\Models\Cotisation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class CotisationResource extends Resource
{
    protected static ?string $model = Cotisation::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema
    }

    public static function table(Table $table): Table
    {
        return $table

    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations de la cotisation')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('contrat.numero_contrat')
                                    ->icon('heroicon-o-document-text'),

                                Infolists\Components\TextEntry::make('contrat.souscripteur.nom_complet')
                                    ->icon('heroicon-o-user'),

                                Infolists\Components\TextEntry::make('date_echeance')
                                    ->date()
                                    ->icon('heroicon-o-calendar'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Montants')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('montant_due')
                                    ->money('EUR')
                                    ->icon('heroicon-o-currency-euro'),

                                Infolists\Components\TextEntry::make('montant_paye')
                                    ->money('EUR')
                                    ->icon('heroicon-o-banknotes'),

                                Infolists\Components\TextEntry::make('montant_restant')
                                    ->money('EUR')
                                    ->icon('heroicon-o-exclamation-circle'),

                                Infolists\Components\TextEntry::make('montant_total_du')
                                    ->money('EUR')
                                    ->icon('heroicon-o-currency-dollar'),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('penalite_retard')
                                    ->money('EUR')
                                    ->icon('heroicon-o-exclamation-triangle'),

                                Infolists\Components\TextEntry::make('interets_moratoires')
                                    ->money('EUR')
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Statut et paiement')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('statut_paiement')
                                    ->badge()
                                    ->icon('heroicon-o-status-online'),

                                Infolists\Components\TextEntry::make('mode_paiement')
                                    ->icon('heroicon-o-credit-card'),

                                Infolists\Components\TextEntry::make('date_paiement')
                                    ->date()
                                    ->icon('heroicon-o-calendar'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\IconEntry::make('est_payee')
                                    ->label('Payée')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle'),

                                Infolists\Components\IconEntry::make('est_en_retard')
                                    ->label('En retard')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-exclamation-triangle')
                                    ->falseIcon('heroicon-o-clock'),

                                Infolists\Components\IconEntry::make('est_rappele')
                                    ->label('Relancée')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-bell-alert')
                                    ->falseIcon('heroicon-o-bell'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Détails')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('reference_paiement')
                                    ->icon('heroicon-o-hashtag'),

                                Infolists\Components\TextEntry::make('numero_facture')
                                    ->icon('heroicon-o-document'),
                            ]),

                        Infolists\Components\TextEntry::make('jours_retard')
                            ->label('Jours de retard')
                            ->suffix(' jours')
                            ->icon('heroicon-o-clock'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaiementRelationManager::class,
            RelationManagers\CommissionRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCotisations::route('/'),
            'create' => Pages\CreateCotisation::route('/create'),
            'edit' => Pages\EditCotisation::route('/{record}/edit'),
            'view' => Pages\ViewCotisation::route('/{record}'),
        ];
    }


}
 */
