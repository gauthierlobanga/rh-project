<?php

namespace App\Filament\Resources\Sinistres\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SinistreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components->schema([
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
                                            ->required()
                                            ->prefixIcon('heroicon-o-status-online'),
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
                                        SpatieMediaLibraryFileUpload::make('documents_sinistre')
                                            ->label('Documents du sinistre')
                                            ->collection('documents_sinistre')
                                            ->multiple()
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                            ->downloadable()
                                            ->previewable(true),

                                        SpatieMediaLibraryFileUpload::make('rapports_expertise')
                                            ->label('Rapports d\'expertise')
                                            ->collection('rapports_expertise')
                                            ->multiple()
                                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                            ->downloadable(),

                                        SpatieMediaLibraryFileUpload::make('preuves')
                                            ->label('Preuves')
                                            ->collection('preuves')
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

/*
 <?php

namespace App\Filament\Resources;

use App\Filament\Resources\SinistreResource\Pages;
use App\Filament\Resources\SinistreResource\RelationManagers;
use App\Models\Sinistre;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Support\Facades\Auth;

class SinistreResource extends Resource
{
    protected static ?string $model = Sinistre::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Sinistres';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form

    }

    public static function table(Table $table): Table
    {
        return $table

    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Identification du sinistre')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('numero_sinistre')
                                    ->icon('heroicon-o-hashtag'),

                                Infolists\Components\TextEntry::make('type_sinistre')
                                    ->badge()
                                    ->icon('heroicon-o-tag'),

                                Infolists\Components\TextEntry::make('statut_sinistre')
                                    ->badge()
                                    ->icon('heroicon-o-status-online'),

                                Infolists\Components\IconEntry::make('est_fraude_suspectee')
                                    ->label('Fraude suspectée')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-shield-exclamation')
                                    ->falseIcon('heroicon-o-shield-check'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Informations du contrat')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('contrat.numero_contrat')
                                    ->icon('heroicon-o-document-text'),

                                Infolists\Components\TextEntry::make('contrat.souscripteur.nom_complet')
                                    ->icon('heroicon-o-user'),

                                Infolists\Components\TextEntry::make('contrat.capital_assure')
                                    ->money('EUR')
                                    ->icon('heroicon-o-currency-euro'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Dates importantes')
                    ->schema([
                        Infolists\Components\Grid::make(5)
                            ->schema([
                                Infolists\Components\TextEntry::make('date_survenance')
                                    ->date()
                                    ->icon('heroicon-o-calendar'),

                                Infolists\Components\TextEntry::make('date_declaration')
                                    ->date()
                                    ->icon('heroicon-o-calendar'),

                                Infolists\Components\TextEntry::make('date_notification')
                                    ->date()
                                    ->icon('heroicon-o-bell'),

                                Infolists\Components\TextEntry::make('date_traitement')
                                    ->date()
                                    ->icon('heroicon-o-cog'),

                                Infolists\Components\TextEntry::make('date_indemnisation')
                                    ->date()
                                    ->icon('heroicon-o-banknotes'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Montants')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('montant_reclame')
                                    ->money('EUR')
                                    ->icon('heroicon-o-currency-euro'),

                                Infolists\Components\TextEntry::make('montant_accordee')
                                    ->money('EUR')
                                    ->icon('heroicon-o-check-circle'),

                                Infolists\Components\TextEntry::make('montant_indemnise')
                                    ->money('EUR')
                                    ->icon('heroicon-o-banknotes'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Expert et documents')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('expert.nom_complet')
                                    ->icon('heroicon-o-user-circle'),

                                Infolists\Components\TextEntry::make('numero_virement')
                                    ->icon('heroicon-o-credit-card'),
                            ]),

                        Infolists\Components\TextEntry::make('documents_recus_count')
                            ->label('Documents reçus')
                            ->state(fn ($record) => count($record->documents_recus ?? []))
                            ->suffix(' / ' . count($record->documents_requis ?? []))
                            ->icon('heroicon-o-document'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaiementRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSinistres::route('/'),
            'create' => Pages\CreateSinistre::route('/create'),
            'edit' => Pages\EditSinistre::route('/{record}/edit'),
            'view' => Pages\ViewSinistre::route('/{record}'),
        ];
    }


}
 */
