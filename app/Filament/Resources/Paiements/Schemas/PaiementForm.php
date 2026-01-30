<?php

namespace App\Filament\Resources\Paiements\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaiementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('echeance_id')
                    ->required()
                    ->numeric(),
                TextInput::make('emprunt_id')
                    ->required()
                    ->numeric(),
                TextInput::make('montant_paye')
                    ->required()
                    ->numeric(),
                DatePicker::make('date_paiement')
                    ->required(),
                TextInput::make('mode_paiement')
                    ->required(),
                TextInput::make('reference_paiement'),
                Toggle::make('est_partiel')
                    ->required(),
                TextInput::make('montant_restant')
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
