<?php

namespace App\Filament\Resources\Emprunts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EmpruntsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Client')
                    ->sortable(),
                TextColumn::make('montant_emprunt')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('date_fin_remboursement')
                    ->date()
                    ->sortable(),
                TextColumn::make('taux_interet_annuel')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('type_amortissement')
                    ->searchable(),
                TextColumn::make('frequence_paiement')
                    ->searchable(),
                TextColumn::make('date_debut')
                    ->date()
                    ->sortable(),
                TextColumn::make('duree_mois')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('frais_dossier')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('frais_assurance')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('frais_notaire')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('frais_autres')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('montant_mensualite')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_interets')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_a_rembourser')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_frais')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('montant_total_du')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('taeg')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('date_approbation')
                    ->date()
                    ->sortable(),
                TextColumn::make('date_signature')
                    ->date()
                    ->sortable(),
                TextColumn::make('date_deblocage')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('taux_interet_mensuel')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
