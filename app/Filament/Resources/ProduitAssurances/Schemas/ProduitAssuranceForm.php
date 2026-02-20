<?php

namespace App\Filament\Resources\ProduitAssurances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ProduitAssuranceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Produit')
                    ->tabs([
                        Tab::make('Description')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Identification')
                                    ->schema([
                                        TextInput::make('code_produit')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-hashtag'),

                                        TextInput::make('nom_produit')
                                            ->required()
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-tag'),

                                        Select::make('categorie')
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

                                        Toggle::make('est_actif')
                                            ->label('Produit actif')
                                            ->required()
                                            ->inline(false)
                                            ->onIcon('heroicon-o-check-circle')
                                            ->offIcon('heroicon-o-x-circle'),
                                    ])->columns(2),

                                Section::make('Descriptions')
                                    ->schema([
                                        Textarea::make('description_courte')
                                            ->rows(2)
                                            ->maxLength(500)
                                            ->helperText('Description courte pour les listes (max 500 caractères)'),

                                        RichEditor::make('description_longue')
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
                                        TextInput::make('age_entree_minimum')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('ans')
                                            ->prefixIcon('heroicon-o-user-minus'),

                                        TextInput::make('age_entree_maximum')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->suffix('ans')
                                            ->prefixIcon('heroicon-o-user-plus'),

                                        TextInput::make('age_maturite_maximum')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(120)
                                            ->suffix('ans')
                                            ->prefixIcon('heroicon-o-user'),

                                        TextInput::make('capital_minimum')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('€')
                                            ->prefixIcon('heroicon-o-currency-euro'),

                                        TextInput::make('capital_maximum')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('€')
                                            ->prefixIcon('heroicon-o-currency-euro'),

                                        TextInput::make('prime_minimale')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('€')
                                            ->prefixIcon('heroicon-o-banknotes'),

                                        TextInput::make('prime_maximale')
                                            ->numeric()
                                            ->minValue(0)
                                            ->prefix('€')
                                            ->prefixIcon('heroicon-o-banknotes'),
                                    ])->columns(3),

                                Section::make('Garanties et exclusions')
                                    ->schema([
                                        KeyValue::make('garanties_incluses')
                                            ->keyLabel('Garantie')
                                            ->valueLabel('Description')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),

                                        KeyValue::make('exclusions')
                                            ->keyLabel('Exclusion')
                                            ->valueLabel('Description')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),

                                        KeyValue::make('options_disponibles')
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
                                        KeyValue::make('structure_commission')
                                            ->keyLabel('Type de commission')
                                            ->valueLabel('Taux (%)')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),
                                    ]),

                                Section::make('Paramètres actuariels')
                                    ->schema([
                                        KeyValue::make('parametres_actuariels')
                                            ->keyLabel('Paramètre')
                                            ->valueLabel('Valeur')
                                            ->addable(true)
                                            ->deletable(true)
                                            ->editableKeys(true)
                                            ->editableValues(true),

                                        Textarea::make('conditions_particulieres')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Documentation')
                                    ->schema([
                                        TextInput::make('document_contrat_type')
                                            ->maxLength(255)
                                            ->prefixIcon('heroicon-o-document'),

                                        DatePicker::make('date_activation')
                                            ->native(false)
                                            ->prefixIcon('heroicon-o-calendar'),

                                        DatePicker::make('date_desactivation')
                                            ->native(false)
                                            ->prefixIcon('heroicon-o-calendar'),
                                    ])->columns(3),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
