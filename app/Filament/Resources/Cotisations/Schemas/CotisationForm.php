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
                                    ->required(),
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
