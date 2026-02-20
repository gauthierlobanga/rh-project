<?php

namespace App\Filament\Resources\Beneficiaires\Schemas;

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

class BeneficiaireForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations personnelles')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('nom')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('prenom')
                                    ->required()
                                    ->maxLength(255),

                                DatePicker::make('date_naissance')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-cake'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('lien_parente')
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-user-group'),

                                TextInput::make('numero_cni')
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-o-identification'),

                                Select::make('statut_beneficiaire')
                                    ->options([
                                        'actif' => 'Actif',
                                        'inactif' => 'Inactif',
                                        'decede' => 'Décédé',
                                        'inconnu' => 'Inconnu',
                                    ])
                                    ->searchable()
                                    ->default('actif'),
                            ]),
                    ]),

                Section::make('Attribution')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('contrat_id')
                                    ->relationship('contrat', 'numero_contrat')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if ($state) {
                                            $contrat = ContratAssuranceVie::find($state);
                                            if ($contrat) {
                                                /**
                                                 * Calculer le pourcentage d'attribution disponible en fonction des bénéficiaires déjà associés au contrat.
                                                 * Si le pourcentage saisi dépasse le disponible, le limiter automatiquement pour éviter que le total dépasse 100%.
                                                 * Cette logique garantit que les utilisateurs ne peuvent pas attribuer plus de 100% du capital assuré à l'ensemble des bénéficiaires d'un même contrat.
                                                 */
                                                $beneficiaires = $contrat->beneficiaires()->sum('pourcentage_attribution');
                                                $disponible = 100 - $beneficiaires;
                                                if ($get('pourcentage_attribution') > $disponible) {
                                                    $set('pourcentage_attribution', $disponible);
                                                }
                                            }
                                        }
                                    })
                                    ->prefixIcon('heroicon-o-document-text'),

                                TextInput::make('pourcentage_attribution')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->step(0.01)
                                    ->suffix('%')
                                    ->prefixIcon('heroicon-o-percent-badge'),

                                Toggle::make('est_beneficiaire_primaire')
                                    ->label('Bénéficiaire primaire')
                                    ->inline(false)
                                    ->onIcon('heroicon-o-star')
                                    ->offIcon('heroicon-o-star'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                DatePicker::make('date_effet_attribution')
                                    ->native(false)
                                    ->default(today())
                                    ->prefixIcon('heroicon-o-calendar'),

                                DatePicker::make('date_fin_attribution')
                                    ->native(false)
                                    ->prefixIcon('heroicon-o-calendar'),
                            ]),
                    ]),

                Section::make('Coordonnées')
                    ->schema([
                        KeyValue::make('coordonnees_contact')
                            ->keyLabel('Type')
                            ->valueLabel('Valeur')
                            ->addable(true)
                            ->deletable(true)
                            ->editableKeys(true)
                            ->editableValues(true),

                        Textarea::make('conditions_particulieres')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
