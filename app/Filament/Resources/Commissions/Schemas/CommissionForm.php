<?php

namespace App\Filament\Resources\Commissions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations de la commission')
                    ->schema([
                        Select::make('agent_id')
                            ->relationship('agent', 'nom_complet')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state && ! $get('taux_commission')) {
                                    $agent = \App\Models\Agent::find($state);
                                    if ($agent) {
                                        $set('taux_commission', $agent->taux_commission);
                                    }
                                }
                            }),

                        Grid::make(2)
                            ->schema([
                                Select::make('contrat_id')
                                    ->relationship('contrat', 'numero_contrat')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('cotisation_id')
                                    ->relationship('cotisation', 'id')
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Select::make('type_commission')
                                    ->options([
                                        'acquisition' => 'Acquisition',
                                        'renouvellement' => 'Renouvellement',
                                        'performance' => 'Performance',
                                        'service' => 'Service',
                                        'loyalty' => 'Fidélité',
                                    ])
                                    ->required()
                                    ->prefixIcon('heroicon-o-tag'),

                                Select::make('statut_commission')
                                    ->options([
                                        'calculee' => 'Calculée',
                                        'a_payer' => 'À payer',
                                        'payee' => 'Payée',
                                        'annulee' => 'Annulée',
                                    ])
                                    ->required()
                                    ->prefixIcon('heroicon-o-status-online'),

                                TextInput::make('annee_comptable')
                                    ->numeric()
                                    ->minValue(2000)
                                    ->maxValue(2100)
                                    ->default(date('Y'))
                                    ->prefixIcon('heroicon-o-calendar'),
                            ]),
                    ]),

                Section::make('Calcul de la commission')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('montant_prime')
                                    ->numeric()
                                    ->required()
                                    ->prefix('€')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $taux = $get('taux_commission') ?? 0;
                                        $montant = $state ?? 0;
                                        $commission = $montant * ($taux / 100);
                                        $set('montant_commission', $commission);
                                    })
                                    ->prefixIcon('heroicon-o-banknotes'),

                                TextInput::make('taux_commission')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $montant = $get('montant_prime') ?? 0;
                                        $commission = $montant * ($state / 100);
                                        $set('montant_commission', $commission);
                                    })
                                    ->prefixIcon('heroicon-o-percent-badge'),

                                TextInput::make('montant_commission')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('€')
                                    ->prefixIcon('heroicon-o-currency-dollar'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('taux_tva')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->default(20)
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        $commission = $get('montant_commission') ?? 0;
                                        $tva = $commission * ($state / 100);
                                        $net = $commission - $tva;
                                        $set('montant_tva', $tva);
                                        $set('montant_net', $net);
                                    })
                                    ->prefixIcon('heroicon-o-percent-badge'),

                                TextInput::make('montant_tva')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('€')
                                    ->prefixIcon('heroicon-o-currency-euro'),

                                TextInput::make('montant_net')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('€')
                                    ->prefixIcon('heroicon-o-banknotes'),
                            ]),

                        TextInput::make('mois_comptable')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(12)
                            ->default(date('m'))
                            ->prefixIcon('heroicon-o-calendar'),
                    ]),

                Section::make('Paiement')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('date_calcul')
                                    ->default(today())
                                    ->native(false)
                                    ->required()
                                    ->prefixIcon('heroicon-o-calculator'),

                                DatePicker::make('date_paiement')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-calendar'),
                            ]),

                        TextInput::make('numero_paiement')
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-hashtag'),

                        KeyValue::make('details_calcul'),
                    ]),

                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
