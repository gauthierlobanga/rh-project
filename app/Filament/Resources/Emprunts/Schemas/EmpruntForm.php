<?php

namespace App\Filament\Resources\Emprunts\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;

class EmpruntForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('montant_emprunt')
                    ->required()
                    ->numeric(),
                DatePicker::make('date_fin_remboursement')
                    ->required(),
                TextInput::make('taux_interet_annuel')
                    ->required()
                    ->numeric(),
                TextInput::make('type_amortissement')
                    ->required()
                    ->default('constant'),
                TextInput::make('frequence_paiement')
                    ->required()
                    ->default('mensuel'),
                DatePicker::make('date_debut')
                    ->required(),
                TextInput::make('duree_mois')
                    ->numeric(),
                TextInput::make('frais_dossier')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('frais_assurance')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('frais_notaire')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('frais_autres')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('montant_mensualite')
                    ->numeric(),
                TextInput::make('total_interets')
                    ->numeric(),
                TextInput::make('total_a_rembourser')
                    ->numeric(),
                TextInput::make('total_frais')
                    ->numeric(),
                TextInput::make('montant_total_du')
                    ->numeric(),
                TextInput::make('taeg')
                    ->numeric(),
                DatePicker::make('date_approbation')->native(false)->default(now()),
                DatePicker::make('date_signature')->native(false),
                DatePicker::make('date_deblocage')->native(false),
                Select::make('status')
                    ->searchable()
                    ->options([
                        'en_attente' => 'En attente',
                        'approuve' => 'Approuvé',
                        'refuse' => 'Refuse',
                        'en_cours' => 'En cours',
                        'termine' => 'Terminé',
                        'defaut' => 'Defaut'
                    ])
                    ->preload()
                    ->required()
                    ->default('en_attente'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('taux_interet_mensuel')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
