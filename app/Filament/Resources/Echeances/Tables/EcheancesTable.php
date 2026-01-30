<?php

namespace App\Filament\Resources\Echeances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EcheancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('emprunt_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('numero_echeance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('date_echeance')
                    ->date()
                    ->sortable(),
                IconColumn::make('est_payee')
                    ->boolean(),
                TextColumn::make('date_paiement')
                    ->date()
                    ->sortable(),
                TextColumn::make('capital_initial')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('montant_echeance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('part_interets')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('part_capital')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('capital_restant')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('interets_cumules')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('capital_cumule')
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
