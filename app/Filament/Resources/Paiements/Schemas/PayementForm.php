<?php

namespace App\Filament\Resources\Paiements\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class PayementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Paiement')
                    ->tabs([
                        Tab::make('Informations générales')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Origine du paiement')
                                    ->schema([
                                        Select::make('type_paiement')
                                            ->options([
                                                'cotisation' => 'Cotisation',
                                                'indemnisation' => 'Indemnisation',
                                                'rachat' => 'Rachat',
                                                'frais' => 'Frais',
                                                'remboursement' => 'Remboursement',
                                                'autre' => 'Autre',
                                            ])
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            ->prefixIcon('heroicon-o-tag'),

                                        Grid::make(3)
                                            ->schema([
                                                Select::make('contrat_id')
                                                    ->relationship('contrat', 'numero_contrat')
                                                    ->searchable()
                                                    ->preload()
                                                    ->prefixIcon('heroicon-o-document-text'),

                                                Select::make('cotisation_id')
                                                    ->relationship('cotisation', 'id')
                                                    ->searchable()
                                                    ->preload()
                                                    ->visible(fn ($get) => $get('type_paiement') === 'cotisation')
                                                    ->prefixIcon('heroicon-o-banknotes'),

                                                Select::make('sinistre_id')
                                                    ->relationship('sinistre', 'numero_sinistre')
                                                    ->searchable()
                                                    ->preload()
                                                    ->visible(fn ($get) => $get('type_paiement') === 'indemnisation')
                                                    ->prefixIcon('heroicon-o-exclamation-triangle'),
                                            ]),
                                    ]),

                                Section::make('Montant et dates')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('montant_paiement')
                                                    ->required()
                                                    ->numeric()
                                                    ->prefix('€')
                                                    ->prefixIcon('heroicon-o-currency-euro'),

                                                DatePicker::make('date_paiement')
                                                    ->default(today())
                                                    ->native(false)
                                                    ->required()
                                                    ->prefixIcon('heroicon-o-calendar'),

                                                Select::make('statut_paiement')
                                                    ->options([
                                                        'en_cours' => 'En cours',
                                                        'valide' => 'Validé',
                                                        'refuse' => 'Refusé',
                                                        'annule' => 'Annulé',
                                                        'en_attente' => 'En attente',
                                                    ])
                                                    ->searchable()
                                                    ->required(),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Mode de paiement')
                            ->icon('heroicon-o-credit-card')
                            ->schema([
                                Section::make('Détails du paiement')
                                    ->schema([
                                        Select::make('mode_paiement')
                                            ->options([
                                                'virement' => 'Virement',
                                                'cheque' => 'Chèque',
                                                'carte' => 'Carte bancaire',
                                                'prelevement' => 'Prélèvement',
                                                'especes' => 'Espèces',
                                                'compensation' => 'Compensation',
                                            ])
                                            ->searchable()
                                            ->required()
                                            ->prefixIcon('heroicon-o-credit-card'),

                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('reference_paiement')
                                                    ->maxLength(255)
                                                    ->prefixIcon('heroicon-o-hashtag'),

                                                TextInput::make('numero_cheque')
                                                    ->maxLength(255)
                                                    ->visible(fn ($get) => $get('mode_paiement') === 'cheque')
                                                    ->prefixIcon('heroicon-o-document'),

                                                TextInput::make('numero_virement')
                                                    ->maxLength(255)
                                                    ->visible(fn ($get) => $get('mode_paiement') === 'virement')
                                                    ->prefixIcon('heroicon-o-arrow-path'),
                                            ]),

                                        TextInput::make('titulaire_compte')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-user'),

                                        KeyValue::make('coordonnees_bancaires')
                                            ->keyLabel('Information')
                                            ->valueLabel('Valeur')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),
                                    ]),
                            ]),

                        Tab::make('Validation et récurrence')
                            ->icon('heroicon-o-check-circle')
                            ->schema([
                                Section::make('Validation')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('valide_par')
                                                    ->relationship('validateur', 'name')
                                                    ->searchable()
                                                    ->preload()
                                                    ->prefixIcon('heroicon-o-user-check'),

                                                DatePicker::make('date_validation')
                                                    ->native(false)
                                                    ->prefixIcon('heroicon-o-calendar'),
                                            ]),

                                        TextInput::make('motif_refus')
                                            ->maxLength(255)
                                            ->visible(fn ($get) => $get('statut_paiement') === 'refuse')
                                            ->prefixIcon('heroicon-o-x-circle'),
                                    ]),

                                Section::make('Récurrence')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Toggle::make('est_recurrent')
                                                    ->label('Paiement récurrent')
                                                    ->inline(false)
                                                    ->onIcon('heroicon-o-arrow-path')
                                                    ->offIcon('heroicon-o-clock'),

                                                TextInput::make('frequence_recurrence')
                                                    ->maxLength(255)
                                                    ->visible(fn ($get) => $get('est_recurrent'))
                                                    ->default('mensuelle')
                                                    ->prefixIcon('heroicon-o-clock'),

                                                DatePicker::make('prochaine_echeance')
                                                    ->native(false)
                                                    ->visible(fn ($get) => $get('est_recurrent'))
                                                    ->prefixIcon('heroicon-o-calendar'),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Informations complémentaires')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Détails')
                                    ->schema([
                                        KeyValue::make('details_paiement')
                                            ->keyLabel('Clé')
                                            ->valueLabel('Valeur')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),

                                        Textarea::make('notes')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
