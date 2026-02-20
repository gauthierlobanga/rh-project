<?php

namespace App\Filament\Resources\NotificationAssurances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificationAssuranceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Destinataire')
                    ->schema([
                        Select::make('destinataire_id')
                            ->relationship('destinataire', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon('heroicon-o-user'),

                        Select::make('canal_envoi')
                            ->options([
                                'email' => 'Email',
                                'sms' => 'SMS',
                                'application' => 'Application',
                                'tous' => 'Tous les canaux',
                            ])
                            ->required()
                            ->default('application')
                            ->prefixIcon('heroicon-o-megaphone'),
                    ])->columns(2),

                Section::make('Contenu')
                    ->schema([
                        Select::make('type_notification')
                            ->options([
                                'rappel_paiement' => 'Rappel de paiement',
                                'echeance_proche' => 'Échéance proche',
                                'sinistre_declare' => 'Sinistre déclaré',
                                'alerte_securite' => 'Alerte sécurité',
                                'contrat_active' => 'Contrat activé',
                                'commission_calculee' => 'Commission calculée',
                                'information' => 'Information',
                                'promotion' => 'Promotion',
                                'systeme' => 'Système',
                            ])
                            ->required()
                            ->reactive()
                            ->prefixIcon('heroicon-o-tag'),

                        TextInput::make('titre')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-chat-bubble-left'),

                        Textarea::make('contenu')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        KeyValue::make('donnees_liees')
                            ->keyLabel('Clé')
                            ->valueLabel('Valeur')
                            ->addable(true)
                            ->deletable(true)
                            ->editableKeys(true)
                            ->editableValues(true),
                    ]),

                Section::make('Programmation et priorité')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('est_urgente')
                                    ->label('Urgente')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-exclamation-triangle')
                                    ->offIcon('heroicon-o-clock'),

                                DatePicker::make('date_expiration')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-calendar'),

                                TextInput::make('tentatives_envoi')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->prefixIcon('heroicon-o-arrow-path'),
                            ]),
                    ]),

                Section::make('Statut')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('est_lue')
                                    ->label('Lue')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-eye')
                                    ->offIcon('heroicon-o-eye-slash'),

                                DateTimePicker::make('date_lecture')
                                    ->native(false)
                                    ->disabled(fn ($get) => !$get('est_lue'))
                                    ->prefixIcon('heroicon-o-calendar'),

                                Toggle::make('est_envoyee')
                                    ->label('Envoyée')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-paper-airplane')
                                    ->offIcon('heroicon-o-clock'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('date_envoi')
                                    ->native(false)
                                    ->disabled(fn ($get) => !$get('est_envoyee'))
                                    ->prefixIcon('heroicon-o-calendar'),

                                TextInput::make('erreur_envoi')
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-exclamation-circle'),
                            ]),
                    ]),
            ]);
    }
}
