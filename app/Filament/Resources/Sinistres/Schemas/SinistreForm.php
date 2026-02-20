<?php

namespace App\Filament\Resources\Sinistres\Schemas;

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

class SinistreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Sinistre')
                    ->tabs([
                        Tab::make('Déclaration')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Identification')
                                    ->schema([
                                        TextInput::make('numero_sinistre')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->default('SIN-'.date('Ymd-His'))
                                            ->prefixIcon('heroicon-o-hashtag'),

                                        Select::make('contrat_id')
                                            ->relationship('contrat', 'numero_contrat')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, $set, $get) {
                                                if ($state && ! $get('montant_reclame')) {
                                                    $contrat = \App\Models\ContratAssuranceVie::find($state);
                                                    if ($contrat) {
                                                        $set('montant_reclame', $contrat->capital_assure);
                                                    }
                                                }
                                            }),

                                        Select::make('type_sinistre')
                                            ->options([
                                                'deces' => 'Décès',
                                                'invalidite' => 'Invalidité',
                                                'incapacite' => 'Incapacité',
                                                'rachat' => 'Rachat',
                                                'resiliation' => 'Résiliation',
                                                'autre' => 'Autre',
                                            ])
                                            ->required()
                                            ->prefixIcon('heroicon-o-tag'),

                                        Select::make('statut_sinistre')
                                            ->options([
                                                'declare' => 'Déclaré',
                                                'en_cours_examen' => 'En cours d\'examen',
                                                'documents_manquants' => 'Documents manquants',
                                                'expertise_en_cours' => 'Expertise en cours',
                                                'accepte' => 'Accepté',
                                                'refuse' => 'Refusé',
                                                'indemnise' => 'Indemnisé',
                                                'cloture' => 'Clôturé',
                                            ])
                                            ->required(),
                                    ])->columns(2),

                                Section::make('Dates')
                                    ->schema([
                                        DatePicker::make('date_survenance')
                                            ->required()
                                            ->native(false)
                                            ->prefixIcon('heroicon-o-calendar'),

                                        DatePicker::make('date_declaration')
                                            ->default(today())
                                            ->native(false)
                                            ->required()
                                            ->prefixIcon('heroicon-o-calendar'),

                                        DatePicker::make('date_notification')
                                            ->native(false)
                                            ->prefixIcon('heroicon-o-bell'),

                                        DatePicker::make('date_traitement')
                                            ->native(false)
                                            ->prefixIcon('heroicon-o-cog'),

                                        DatePicker::make('date_indemnisation')
                                            ->native(false)
                                            ->prefixIcon('heroicon-o-banknotes'),
                                    ])->columns(3),

                                Section::make('Description')
                                    ->schema([
                                        Textarea::make('description_sinistre')
                                            ->required()
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Expertise & Traitement')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                Section::make('Expert')
                                    ->schema([
                                        Select::make('expert_id')
                                            ->relationship('expert', 'nom_complet')
                                            ->searchable()
                                            ->preload()
                                            ->prefixIcon('heroicon-o-user-circle'),

                                        Textarea::make('notes_expert')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Montants')
                                    ->schema([
                                        TextInput::make('montant_reclame')
                                            ->required()
                                            ->numeric()
                                            ->prefix('€')
                                            ->prefixIcon('heroicon-o-currency-euro'),

                                        TextInput::make('montant_accordee')
                                            ->numeric()
                                            ->prefix('€')
                                            ->prefixIcon('heroicon-o-check-circle'),

                                        TextInput::make('montant_indemnise')
                                            ->numeric()
                                            ->prefix('€')
                                            ->prefixIcon('heroicon-o-banknotes'),
                                    ])->columns(3),

                                Section::make('Décision')
                                    ->schema([
                                        TextInput::make('motif_refus')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-x-circle'),

                                        TextInput::make('numero_virement')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-credit-card'),

                                        KeyValue::make('beneficiaires_indemnisation')
                                            ->keyLabel('Bénéficiaire')
                                            ->valueLabel('Pourcentage')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),
                                    ])->columns(3),
                            ]),

                        Tab::make('Documents')
                            ->icon('heroicon-o-paper-clip')
                            ->schema([
                                Section::make('Documents requis/reçus')
                                    ->schema([
                                        KeyValue::make('documents_requis')
                                            ->keyLabel('Document')
                                            ->valueLabel('Statut')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),

                                        KeyValue::make('documents_recus')
                                            ->keyLabel('Document')
                                            ->valueLabel('Date de réception')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),
                                    ]),

                                Section::make('Pièces jointes')
                                    ->schema([
                                        FileUpload::make('documents_sinistre')
                                            ->label('Documents du sinistre')
                                            // ->collection('documents_sinistre')
                                            ->multiple()
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                            ->downloadable()
                                            ->previewable(true),

                                        FileUpload::make('rapports_expertise')
                                            ->label('Rapports d\'expertise')
                                            // ->collection('rapports_expertise')
                                            ->multiple()
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                            ->downloadable(),

                                        FileUpload::make('preuves')
                                            ->label('Preuves')
                                            // ->collection('preuves')
                                            ->multiple()
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'video/mp4'])
                                            ->downloadable(),
                                    ])->columns(3),
                            ]),

                        Tab::make('Contrôle & Audit')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Section::make('Contrôle fraude')
                                    ->schema([
                                        Toggle::make('est_fraude_suspectee')
                                            ->label('Fraude suspectée')
                                            ->inline(false)
                                            ->onIcon('heroicon-o-shield-exclamation')
                                            ->offIcon('heroicon-o-shield-check'),

                                        Textarea::make('notes_fraude')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Commentaires internes')
                                    ->schema([
                                        Textarea::make('commentaires_internes')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
