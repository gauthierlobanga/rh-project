<?php

namespace App\Filament\Resources\Cotisations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CotisationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contrat.id')
                    ->searchable(),
                TextColumn::make('date_echeance')
                    ->date()
                    ->sortable(),
                TextColumn::make('date_paiement')
                    ->date()
                    ->sortable(),
                TextColumn::make('montant_due')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('montant_paye')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('statut_paiement')
                    ->searchable(),
                TextColumn::make('numero_facture')
                    ->searchable(),
                TextColumn::make('penalite_retard')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('interets_moratoires')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('mode_paiement')
                    ->searchable(),
                TextColumn::make('reference_paiement')
                    ->searchable(),
                TextColumn::make('date_encaissement')
                    ->date()
                    ->sortable(),
                IconColumn::make('est_rappele')
                    ->boolean(),
                TextColumn::make('date_rappel')
                    ->date()
                    ->sortable(),
                TextColumn::make('nombre_relances')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
