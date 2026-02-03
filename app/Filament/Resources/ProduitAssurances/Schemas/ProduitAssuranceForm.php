<?php

namespace App\Filament\Resources\ProduitAssurances\Schemas;

use Filament\Schemas\Schema;

class ProduitAssuranceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
/*
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProduitAssuranceResource\Pages;
use App\Filament\Resources\ProduitAssuranceResource\RelationManagers;
use App\Models\ProduitAssurance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ProduitAssuranceResource extends Resource
{
    protected static ?string $model = ProduitAssurance::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Produit')
                    ->tabs([
                        Tab::make('Description')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Identification')
                                    ->schema([
                                        Forms\Components\TextInput::make('code_produit')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-hashtag'),

                                        Forms\Components\TextInput::make('nom_produit')
                                            ->required()
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-tag'),

                                        Forms\Components\Select::make('categorie')
                                            ->options([
                                                'vie_entier' => 'Vie entière',
                                                'temporaire' => 'Temporaire',
                                                'mixte' => 'Mixte',
                                                'epargne' => 'Épargne',
                                                'retraite' => 'Retraite',
                                                'capitalisation' => 'Capitalisation',
                                            ])
                                            ->required()
                                            ->prefixIcon('heroicon-o-rectangle-group'),

                                        Forms\Components\Toggle::make('est_actif')
                                            ->label('Produit actif')
                                            ->required()
                                            ->inline(false)
                                            ->onIcon('heroicon-o-check-circle')
                                            ->offIcon('heroicon-o-x-circle'),
                                    ])->columns(2),

                                Section::make('Descriptions')
                                    ->schema([
                                        Forms\Components\Textarea::make('description_courte')
                                            ->rows(2)
                                            ->maxLength(500)
                                            ->helperText('Description courte pour les listes (max 500 caractères)'),

                                        Forms\Components\RichEditor::make('description_longue')
                                            ->toolbarButtons([
                                                'bold', 'italic', 'underline',
                                                'bulletList', 'orderedList',
                                                'link',
                                            ])
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Tab::make('Conditions')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('Limites d\'âge et capital')
                                    ->schema([
                                        Forms\Components\TextInput::make('age_entree_minimum')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('ans')
                                            ->prefixIcon('heroicon-o-user-minus'),

                                        Forms\Components\TextInput::make('age_entree_maximum')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('ans')
                                            ->prefixIcon('heroicon-o-user-plus'),

                                        Forms\Components\TextInput::make('age_maturite_maximum')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(120)
                                            ->suffix('ans')
                                            ->prefixIcon('heroicon-o-user'),

                                        Forms\Components\TextInput::make('capital_minimum')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('€')
                                            ->prefixIcon('heroicon-o-currency-euro'),

                                        Forms\Components\TextInput::make('capital_maximum')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('€')
                                            ->prefixIcon('heroicon-o-currency-euro'),

                                        Forms\Components\TextInput::make('prime_minimale')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('€')
                                            ->prefixIcon('heroicon-o-banknotes'),

                                        Forms\Components\TextInput::make('prime_maximale')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('€')
                                            ->prefixIcon('heroicon-o-banknotes'),
                                    ])->columns(3),

                                Section::make('Garanties et exclusions')
                                    ->schema([
                                        Forms\Components\KeyValue::make('garanties_incluses')
                                            ->keyLabel('Garantie')
                                            ->valueLabel('Description')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),

                                        Forms\Components\KeyValue::make('exclusions')
                                            ->keyLabel('Exclusion')
                                            ->valueLabel('Description')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),

                                        Forms\Components\KeyValue::make('options_disponibles')
                                            ->keyLabel('Option')
                                            ->valueLabel('Description')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),
                                    ]),
                            ]),

                        Tab::make('Paramètres commerciaux')
                            ->icon('heroicon-o-cog')
                            ->schema([
                                Section::make('Commissions')
                                    ->schema([
                                        Forms\Components\KeyValue::make('structure_commission')
                                            ->keyLabel('Type de commission')
                                            ->valueLabel('Taux (%)')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),
                                    ]),

                                Section::make('Paramètres actuariels')
                                    ->schema([
                                        Forms\Components\KeyValue::make('parametres_actuariels')
                                            ->keyLabel('Paramètre')
                                            ->valueLabel('Valeur')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),

                                        Forms\Components\Textarea::make('conditions_particulieres')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Documentation')
                                    ->schema([
                                        Forms\Components\TextInput::make('document_contrat_type')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-document'),

                                        Forms\Components\DatePicker::make('date_activation')
                                            ->native(false)
                                            ->prefixIcon('heroicon-o-calendar'),

                                        Forms\Components\DatePicker::make('date_desactivation')
                                            ->native(false)
                                            ->prefixIcon('heroicon-o-calendar'),
                                    ])->columns(3),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code_produit')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-hashtag'),

                Tables\Columns\TextColumn::make('nom_produit')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-tag'),

                Tables\Columns\TextColumn::make('categorie')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vie_entier' => 'success',
                        'temporaire' => 'warning',
                        'mixte' => 'info',
                        'epargne' => 'primary',
                        'retraite' => 'danger',
                        'capitalisation' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('est_actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('capital_minimum')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('capital_maximum')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('contrats_count')
                    ->counts('contrats')
                    ->label('Contrats')
                    ->icon('heroicon-o-document-text'),

                Tables\Columns\TextColumn::make('prime_moyenne')
                    ->state(fn ($record) => $record->prime_moyenne)
                    ->money('EUR')
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categorie')
                    ->options([
                        'vie_entier' => 'Vie entière',
                        'temporaire' => 'Temporaire',
                        'mixte' => 'Mixte',
                        'epargne' => 'Épargne',
                        'retraite' => 'Retraite',
                        'capitalisation' => 'Capitalisation',
                    ]),

                Tables\Filters\TernaryFilter::make('est_actif')
                    ->label('Actif'),

                Tables\Filters\Filter::make('date_activation')
                    ->form([
                        Forms\Components\DatePicker::make('activated_from')
                            ->label('Activé après'),
                        Forms\Components\DatePicker::make('activated_until')
                            ->label('Activé avant'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['activated_from'], fn ($q, $date) => $q->whereDate('date_activation', '>=', $date))
                            ->when($data['activated_until'], fn ($q, $date) => $q->whereDate('date_activation', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('simuler_prime')
                    ->icon('heroicon-o-calculator')
                    ->form([
                        Forms\Components\TextInput::make('capital')
                            ->numeric()
                            ->required()
                            ->label('Capital assuré')
                            ->prefix('€'),
                        Forms\Components\TextInput::make('age')
                            ->numeric()
                            ->required()
                            ->label('Âge du souscripteur')
                            ->suffix('ans'),
                        Forms\Components\TextInput::make('duree')
                            ->numeric()
                            ->required()
                            ->label('Durée du contrat')
                            ->suffix('ans'),
                    ])
                    ->action(function ($record, array $data) {
                        $prime = $record->calculerPrimeTheorique(
                            $data['capital'],
                            $data['age'],
                            $data['duree']
                        );

                        Notification::make()
                            ->title('Simulation de prime')
                            ->body("Prime théorique: " . number_format($prime, 2, ',', ' ') . " €")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activer')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['est_actif' => true]);
                            }
                        }),
                    Tables\Actions\BulkAction::make('desactiver')
                        ->icon('heroicon-o-x-circle')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['est_actif' => false]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('nom_produit')
            ->striped();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Identification')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('code_produit')
                                    ->icon('heroicon-o-hashtag'),

                                Infolists\Components\TextEntry::make('nom_produit')
                                    ->icon('heroicon-o-tag'),

                                Infolists\Components\TextEntry::make('categorie')
                                    ->badge()
                                    ->icon('heroicon-o-rectangle-group'),

                                Infolists\Components\IconEntry::make('est_actif')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-x-circle'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Limites')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('age_entree_minimum')
                                    ->suffix(' ans')
                                    ->icon('heroicon-o-user-minus'),

                                Infolists\Components\TextEntry::make('age_entree_maximum')
                                    ->suffix(' ans')
                                    ->icon('heroicon-o-user-plus'),

                                Infolists\Components\TextEntry::make('age_maturite_maximum')
                                    ->suffix(' ans')
                                    ->icon('heroicon-o-user'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('capital_minimum')
                                    ->money('EUR')
                                    ->icon('heroicon-o-currency-euro'),

                                Infolists\Components\TextEntry::make('capital_maximum')
                                    ->money('EUR')
                                    ->icon('heroicon-o-currency-euro'),

                                Infolists\Components\TextEntry::make('prime_minimale')
                                    ->money('EUR')
                                    ->icon('heroicon-o-banknotes'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Statistiques')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('contrats_count')
                                    ->label('Nombre de contrats')
                                    ->icon('heroicon-o-document-text'),

                                Infolists\Components\TextEntry::make('prime_moyenne')
                                    ->label('Prime moyenne')
                                    ->money('EUR')
                                    ->icon('heroicon-o-banknotes'),

                                Infolists\Components\TextEntry::make('capital_moyen')
                                    ->label('Capital moyen')
                                    ->money('EUR')
                                    ->icon('heroicon-o-currency-euro'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Dates')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('date_activation')
                                    ->date()
                                    ->icon('heroicon-o-calendar'),

                                Infolists\Components\TextEntry::make('date_desactivation')
                                    ->date()
                                    ->icon('heroicon-o-calendar'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->dateTime()
                                    ->icon('heroicon-o-calendar'),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ContratsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProduitAssurances::route('/'),
            'create' => Pages\CreateProduitAssurance::route('/create'),
            'edit' => Pages\EditProduitAssurance::route('/{record}/edit'),
            'view' => Pages\ViewProduitAssurance::route('/{record}'),
        ];
    }
}
*/
