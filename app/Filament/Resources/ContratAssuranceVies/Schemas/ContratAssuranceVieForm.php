<?php

namespace App\Filament\Resources\ContratAssuranceVies\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

class ContratAssuranceVieForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Step::make('Informations générales')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make('Identification')
                                ->schema([
                                    TextInput::make('numero_contrat')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(255)
                                        ->prefixIcon('heroicon-o-hashtag'),

                                    Select::make('souscripteur_id')
                                        ->relationship('souscripteur', 'utilisateur.name')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->createOptionForm([
                                            TextInput::make('nom')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('prenom')
                                                ->required()
                                                ->maxLength(255),
                                            TextInput::make('email')
                                                ->email()
                                                ->required()
                                                ->maxLength(255),
                                        ])
                                        ->prefixIcon('heroicon-o-user'),

                                    Select::make('produit_id')
                                        ->relationship('produit', 'nom_produit')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->prefixIcon('heroicon-o-cube'),

                                    Select::make('agent_id')
                                        ->relationship('agent', 'utilisateur.name')
                                        ->searchable()
                                        ->preload()
                                        ->prefixIcon('heroicon-o-user-circle'),
                                ])->columns(2),

                            Section::make('Dates')
                                ->schema([
                                    DatePicker::make('date_effet')
                                        ->required()
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar'),

                                    DatePicker::make('date_echeance')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-calendar-days'),

                                    DatePicker::make('date_signature')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-pencil'),

                                    DatePicker::make('date_validation')
                                        ->native(false)
                                        ->prefixIcon('heroicon-o-check'),
                                ])->columns(2),
                        ]),

                    Step::make('Conditions financières')
                        ->icon('heroicon-o-banknotes')
                        ->schema([
                            Section::make('Capital et primes')
                                ->schema([
                                    TextInput::make('capital_assure')
                                        ->required()
                                        ->numeric()
                                        ->prefix('€')
                                        ->prefixIcon('heroicon-o-currency-euro'),

                                    TextInput::make('prime_annuelle')
                                        ->required()
                                        ->numeric()
                                        ->prefix('€')
                                        ->prefixIcon('heroicon-o-banknotes'),

                                    Select::make('frequence_paiement')
                                        ->options([
                                            'mensuelle' => 'Mensuelle',
                                            'trimestrielle' => 'Trimestrielle',
                                            'semestrielle' => 'Semestrielle',
                                            'annuelle' => 'Annuelle',
                                        ])
                                        ->required()
                                        ->prefixIcon('heroicon-o-clock'),

                                    TextInput::make('montant_periodicite')
                                        ->numeric()
                                        ->prefix('€')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->afterStateUpdated(function ($state, $set, $get) {
                                            $prime = $get('prime_annuelle');
                                            $frequence = $get('frequence_paiement');

                                            if ($prime && $frequence) {
                                                $montants = [
                                                    'mensuelle' => $prime / 12,
                                                    'trimestrielle' => $prime / 4,
                                                    'semestrielle' => $prime / 2,
                                                    'annuelle' => $prime,
                                                ];
                                                $set('montant_periodicite', $montants[$frequence] ?? $prime);
                                            }
                                        }),
                                ])->columns(2),

                            Section::make('Frais')
                                ->schema([
                                    TextInput::make('frais_gestion')
                                        ->numeric()
                                        ->suffix('%')
                                        ->prefixIcon('heroicon-o-cog'),

                                    TextInput::make('frais_entree')
                                        ->numeric()
                                        ->suffix('%')
                                        ->prefixIcon('heroicon-o-arrow-right-circle'),

                                    TextInput::make('frais_sortie')
                                        ->numeric()
                                        ->suffix('%')
                                        ->prefixIcon('heroicon-o-arrow-left-circle'),

                                    TextInput::make('participation_benefices')
                                        ->numeric()
                                        ->suffix('%')
                                        ->prefixIcon('heroicon-o-chart-bar'),
                                ])->columns(2),
                        ]),

                    Step::make('Configuration')
                        ->icon('heroicon-o-cog')
                        ->schema([
                            Section::make('Paramètres')
                                ->schema([
                                    Select::make('statut_contrat')
                                        ->options([
                                            'actif' => 'Actif',
                                            'en_attente' => 'En attente',
                                            'suspendu' => 'Suspendu',
                                            'resilie' => 'Résilié',
                                            'expire' => 'Expiré',
                                        ])
                                        ->required()
                                        ->prefixIcon('heroicon-o-status-online'),

                                    Select::make('mode_paiement')
                                        ->options([
                                            'prelevement' => 'Prélèvement',
                                            'virement' => 'Virement',
                                            'cheque' => 'Chèque',
                                            'especes' => 'Espèces',
                                            'carte' => 'Carte bancaire',
                                        ])
                                        ->prefixIcon('heroicon-o-credit-card'),

                                    TextInput::make('duree_contrat')
                                        ->numeric()
                                        ->suffix('ans')
                                        ->prefixIcon('heroicon-o-clock'),
                                ])->columns(3),

                            Section::make('Informations complémentaires')
                                ->schema([
                                    TextInput::make('numero_police')
                                        ->maxLength(255)
                                        ->prefixIcon('heroicon-o-shield-check'),

                                    KeyValue::make('coordonnees_paiement'),

                                    KeyValue::make('options_souscrites'),

                                    Textarea::make('conditions_particulieres')
                                        ->rows(3),

                                    KeyValue::make('parametres_calcul'),
                                ]),
                        ]),
                ])->columnSpanFull(),
            ]);
    }
}
