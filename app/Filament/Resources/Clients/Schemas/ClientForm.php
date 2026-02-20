<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Client')
                    ->tabs([
                        Tab::make('Informations personnelles')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make('Identité')
                                    ->schema([
                                        Select::make('user_id')
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

                                        TextInput::make('reference_client')
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-hashtag'),

                                        Select::make('type_client')
                                            ->options([
                                                'particulier' => 'Particulier',
                                                'professionnel' => 'Professionnel',
                                            ])
                                            ->required()
                                            ->prefixIcon('heroicon-o-tag'),

                                        Select::make('civilite')
                                            ->options([
                                                'M' => 'Monsieur',
                                                'Mme' => 'Madame',
                                                'Mlle' => 'Mademoiselle',
                                            ])
                                            ->prefixIcon('heroicon-o-user'),

                                        TextInput::make('nom')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('prenom')
                                            ->required()
                                            ->maxLength(255),
                                    ])->columns(3),

                                Section::make('État civil')
                                    ->schema([
                                        DatePicker::make('date_naissance')
                                            ->native(false)
                                            ->required()
                                            ->prefixIcon('heroicon-o-cake'),

                                        TextInput::make('lieu_naissance')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-map-pin'),

                                        TextInput::make('nationalite')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-flag'),

                                        Select::make('situation_familiale')
                                            ->options([
                                                'celibataire' => 'Célibataire',
                                                'marie' => 'Marié(e)',
                                                'divorce' => 'Divorcé(e)',
                                                'veuf' => 'Veuf/Veuve',
                                                'concubinage' => 'Concubinage',
                                                'pacs' => 'PACS',
                                            ])
                                            ->prefixIcon('heroicon-o-heart'),

                                        TextInput::make('nombre_enfants')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefixIcon('heroicon-o-user-group'),
                                    ])->columns(3),

                                Section::make('Pièces d\'identité')
                                    ->schema([
                                        TextInput::make('numero_cni')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-identification'),

                                        DatePicker::make('date_expiration_cni')
                                            ->native(false)
                                            ->prefixIcon('heroicon-o-calendar'),

                                        Toggle::make('kyc_verifie')
                                            ->label('KYC Vérifié')
                                            ->inline(false)
                                            ->onIcon('heroicon-o-check-circle')
                                            ->offIcon('heroicon-o-x-circle'),

                                        DatePicker::make('date_verification_kyc')
                                            ->native(false)
                                            ->disabled(fn($get) => ! $get('kyc_verifie'))
                                            ->prefixIcon('heroicon-o-calendar'),
                                    ])->columns(2),
                            ]),

                        Tab::make('Coordonnées')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Section::make('Contact')
                                    ->schema([
                                        TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-envelope'),

                                        TextInput::make('telephone_fixe')
                                            ->tel()
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-phone'),

                                        TextInput::make('telephone_mobile')
                                            ->tel()
                                            ->required()
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-device-phone-mobile'),
                                    ])->columns(3),

                                Section::make('Adresse')
                                    ->schema([
                                        Select::make('adresse.rue')
                                            ->label('Rue')
                                            ->maxLength(255),

                                        Select::make('adresse.complement')
                                            ->label('Complément')

                                            ->maxLength(255),

                                        Select::make('adresse.code_postal')
                                            ->label('Code postal')
                                            ->maxLength(10),

                                        Select::make('adresse.ville')
                                            ->label('Ville')
                                            ->maxLength(255),

                                        Select::make('adresse.pays')
                                            ->label('Pays')
                                            ->maxLength(255)
                                            ->default('France'),
                                    ])->columns(2),

                                Section::make('Bancaire')
                                    ->schema([
                                        TextInput::make('coordonnees_bancaires.iban')
                                            ->label('IBAN')
                                            ->maxLength(34),

                                        TextInput::make('coordonnees_bancaires.bic')
                                            ->label('BIC')
                                            ->maxLength(11),

                                        TextInput::make('coordonnees_bancaires.titulaire')
                                            ->label('Titulaire du compte')
                                            ->maxLength(255),

                                        TextInput::make('coordonnees_bancaires.banque')
                                            ->label('Banque')
                                            ->maxLength(255),
                                    ])->columns(2),
                            ]),

                        Tab::make('Profession & Finance')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Section::make('Profession')
                                    ->schema([
                                        TextInput::make('profession')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-briefcase'),

                                        TextInput::make('revenu_annuel')
                                            ->numeric()
                                            ->prefix('€')
                                            ->prefixIcon('heroicon-o-banknotes'),

                                        KeyValue::make('profil_risque'),
                                    ])->columns(2),

                                Section::make('Gestion')
                                    ->schema([
                                        Select::make('agent_id')
                                            ->relationship('agent', 'utilisateur.name')
                                            ->searchable()
                                            ->preload()
                                            ->prefixIcon('heroicon-o-user-circle'),

                                        Select::make('source_acquisition')
                                            ->options([
                                                'reference' => 'Référence',
                                                'publicite' => 'Publicité',
                                                'reseau' => 'Réseau',
                                                'internet' => 'Internet',
                                                'autre' => 'Autre',
                                            ])
                                            ->prefixIcon('heroicon-o-magnifying-glass'),

                                        Textarea::make('notes')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ])->columns(2),
                            ]),

                        Tab::make('Documents')
                            ->icon('heroicon-o-document')
                            ->schema([
                                Section::make('Pièces jointes')
                                    ->schema([
                                        FileUpload::make('pieces_identite')
                                            ->label('Pièces d\'identité')
                                            // ->collection('pieces_identite')
                                            ->multiple()
                                            ->maxFiles(5)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                                            ->downloadable()
                                            ->previewable(true),

                                        FileUpload::make('justificatifs_domicile')
                                            ->label('Justificatifs de domicile')
                                            // ->collection('justificatifs_domicile')
                                            ->multiple()
                                            ->maxFiles(3)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                                            ->downloadable(),

                                        FileUpload::make('documents_bancaires')
                                            ->label('Documents bancaires')
                                            // ->collection('documents_bancaires')
                                            ->multiple()
                                            ->maxFiles(3)
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                                            ->downloadable(),

                                        FileUpload::make('autres_documents')
                                            ->label('Autres documents')
                                            // ->collection('autres_documents')
                                            ->multiple()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf', 'application/msword'])
                                            ->downloadable(),
                                    ])->columns(2),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
