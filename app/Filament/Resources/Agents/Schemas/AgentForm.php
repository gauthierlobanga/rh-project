<?php

namespace App\Filament\Resources\Agents\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class AgentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Agent')
                    ->tabs([
                        Tab::make('Informations professionnelles')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Section::make('Identité')
                                    ->schema([
                                        Select::make('utilisateur_id')
                                            ->relationship('utilisateur', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->required()
                                                    ->maxLength(255),
                                                TextInput::make('email')
                                                    ->email()
                                                    ->required()
                                                    ->unique()
                                                    ->maxLength(255),
                                                TextInput::make('password')
                                                    ->password()
                                                    ->required()
                                                    ->minLength(8),
                                            ]),

                                        TextInput::make('matricule_agent')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-identification'),

                                        Select::make('statut_agent')
                                            ->options([
                                                'actif' => 'Actif',
                                                'inactif' => 'Inactif',
                                                'suspendu' => 'Suspendu',
                                                'en_conge' => 'En congé',
                                                'formation' => 'En formation',
                                            ])
                                            ->required()
                                            ->prefixIcon('heroicon-o-status-online'),
                                    ])->columns(3),

                                Section::make('Agrément')
                                    ->schema([
                                        TextInput::make('numero_agrement')
                                            ->required()
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-shield-check'),

                                        DatePicker::make('date_expiration_agrement')
                                            ->required()
                                            ->native(false)
                                            ->minDate(today())
                                            ->prefixIcon('heroicon-o-calendar'),

                                        TextInput::make('agence_affectation')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-building-office'),
                                    ])->columns(3),

                                Section::make('Spécialisations')
                                    ->schema([
                                        KeyValue::make('specialisations')
                                            ->keyLabel('Domaine')
                                            ->valueLabel('Niveau')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),

                                        KeyValue::make('coordonnees_professionnelles')
                                            ->keyLabel('Type')
                                            ->valueLabel('Valeur')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),
                                    ]),
                            ]),

                        Tab::make('Performance')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([
                                Section::make('Objectifs')
                                    ->schema([
                                        TextInput::make('objectif_annuel')
                                            ->numeric()
                                            ->prefix('€')
                                            ->prefixIcon('heroicon-o-flag'),

                                        TextInput::make('taux_commission')
                                            ->numeric()
                                            ->suffix('%')
                                            ->prefixIcon('heroicon-o-percent-badge'),

                                        TextInput::make('performance_annuelle')
                                            ->numeric()
                                            ->suffix('%')
                                            ->disabled()
                                            ->prefixIcon('heroicon-o-chart-bar'),
                                    ])->columns(3),

                                Section::make('Statistiques')
                                    ->schema([
                                        TextEntry::make('nombre_contrats')
                                            ->label('Nombre de contrats')
                                            ->content(fn ($record) => $record?->nombre_contrats ?? '0'),

                                        TextEntry::make('valeur_portefeuille')
                                            ->label('Valeur du portefeuille')
                                            ->content(fn ($record) => isset($record) ?
                                                number_format($record->valeur_portefeuille, 2, ',', ' ').' €' :
                                                '0,00 €'
                                            ),

                                        TextEntry::make('total_commissions')
                                            ->label('Total des commissions')
                                            ->content(fn ($record) => isset($record) ?
                                                number_format($record->total_commissions, 2, ',', ' ').' €' :
                                                '0,00 €'
                                            ),

                                        TextEntry::make('taux_conversion')
                                            ->label('Taux de conversion')
                                            ->content(fn ($record) => isset($record) && $record->taux_conversion ?
                                                number_format($record->taux_conversion, 1, ',', ' ').' %' :
                                                '0 %'
                                            ),
                                    ])->columns(2),
                            ]),

                        Tab::make('Documents')
                            ->icon('heroicon-o-document')
                            ->schema([
                                Section::make('Fichiers')
                                    ->schema([
                                        FileUpload::make('agrement_document')
                                            ->label('Document d\'agrément')
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                            ->downloadable()
                                            ->previewable(false),

                                        FileUpload::make('cv_document')
                                            ->label('Curriculum Vitae')
                                            ->acceptedFileTypes(['application/pdf', 'application/msword'])
                                            ->downloadable()
                                            ->previewable(false),

                                        FileUpload::make('contrat_travail')
                                            ->label('Contrat de travail')
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                            ->downloadable()
                                            ->previewable(false),
                                    ])->columns(3),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
