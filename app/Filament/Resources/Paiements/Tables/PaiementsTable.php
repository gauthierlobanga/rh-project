<?php

namespace App\Filament\Resources\Paiements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaiementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('echeance_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('emprunt_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('montant_paye')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('date_paiement')
                    ->date()
                    ->sortable(),
                TextColumn::make('mode_paiement')
                    ->searchable(),
                TextColumn::make('reference_paiement')
                    ->searchable(),
                IconColumn::make('est_partiel')
                    ->boolean(),
                TextColumn::make('montant_restant')
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
