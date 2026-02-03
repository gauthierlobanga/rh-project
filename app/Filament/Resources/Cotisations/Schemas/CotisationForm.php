<?php

namespace App\Filament\Resources\Cotisations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CotisationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('contrat_id')
                    ->relationship('contrat', 'id')
                    ->required(),
                DatePicker::make('date_echeance')
                    ->required(),
                DatePicker::make('date_paiement'),
                TextInput::make('montant_due')
                    ->required()
                    ->numeric(),
                TextInput::make('montant_paye')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('statut_paiement')
                    ->required()
                    ->default('en_attente'),
                TextInput::make('numero_facture'),
                Textarea::make('details_paiement')
                    ->columnSpanFull(),
                TextInput::make('penalite_retard')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('interets_moratoires')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('mode_paiement'),
                TextInput::make('reference_paiement'),
                DatePicker::make('date_encaissement'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Toggle::make('est_rappele')
                    ->required(),
                DatePicker::make('date_rappel'),
                TextInput::make('nombre_relances')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
