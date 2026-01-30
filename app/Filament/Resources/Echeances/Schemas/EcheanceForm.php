<?php

namespace App\Filament\Resources\Echeances\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EcheanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('emprunt_id')
                    ->required()
                    ->numeric(),
                TextInput::make('numero_echeance')
                    ->required()
                    ->numeric(),
                DatePicker::make('date_echeance')
                    ->required(),
                Toggle::make('est_payee')
                    ->required(),
                DatePicker::make('date_paiement'),
                TextInput::make('capital_initial')
                    ->required()
                    ->numeric(),
                TextInput::make('montant_echeance')
                    ->required()
                    ->numeric(),
                TextInput::make('part_interets')
                    ->required()
                    ->numeric(),
                TextInput::make('part_capital')
                    ->required()
                    ->numeric(),
                TextInput::make('capital_restant')
                    ->required()
                    ->numeric(),
                TextInput::make('interets_cumules')
                    ->required()
                    ->numeric(),
                TextInput::make('capital_cumule')
                    ->required()
                    ->numeric(),
            ]);
    }
}
