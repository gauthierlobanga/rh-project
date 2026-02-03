<?php

namespace App\Filament\Resources\Agents\Tables;

use App\Models\Agent;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AgentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('matricule_agent')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                // TextColumn::make('utilisateur.name')
                //     ->searchable()
                //     ->sortable(),

                TextColumn::make('numero_agrement')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-shield-check'),

                TextColumn::make('statut_agent')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'danger',
                        'suspendu' => 'warning',
                        'en_conge' => 'info',
                        'formation' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('date_expiration_agrement')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->date_expiration_agrement &&
                        $record->date_expiration_agrement->lessThan(now()->addDays(30)) ?
                        'danger' : null
                    )
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('agence_affectation')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-office'),

                TextColumn::make('nombre_contrats')
                    ->counts('contrats')
                    ->label('Contrats')
                    ->icon('heroicon-o-document-text'),

                TextColumn::make('valeur_portefeuille')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: ',',
                        thousandsSeparator: ' ',
                    )
                    ->prefix('€ ')
                    ->icon('heroicon-o-currency-euro'),

                TextColumn::make('performance_annuelle')
                    ->suffix('%')
                    ->icon('heroicon-o-chart-bar')
                    ->color(fn ($record) => $record->performance_annuelle >= 100 ? 'success' :
                        ($record->performance_annuelle >= 80 ? 'warning' : 'danger')
                    ),
            ])
            ->filters([
                SelectFilter::make('statut_agent')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                        'suspendu' => 'Suspendu',
                        'en_conge' => 'En congé',
                        'formation' => 'En formation',
                    ]),

                SelectFilter::make('agence_affectation')
                    ->searchable()
                    ->options(fn () => Agent::distinct('agence_affectation')
                        ->pluck('agence_affectation', 'agence_affectation')
                        ->filter()
                    ),

                Filter::make('agrement_expire')
                    ->label('Agrément expiré ou proche expiration')
                    ->query(fn ($query) => $query->whereDate('date_expiration_agrement', '<=', now()->addDays(30))),

                TernaryFilter::make('performance')
                    ->label('Performance')
                    ->placeholder('Tous')
                    ->trueLabel('Objectif atteint (≥100%)')
                    ->falseLabel('Objectif non atteint (<100%)')
                    ->queries(
                        true: fn ($query) => $query->where('performance_annuelle', '>=', 100),
                        false: fn ($query) => $query->where('performance_annuelle', '<', 100),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('calculer_performance')
                    ->icon('heroicon-o-calculator')
                    ->action(fn ($record) => $record->calculerPerformance()),

                Action::make('generer_rapport')
                    ->icon('heroicon-o-document-chart-bar')
                    ->url(fn ($record) => route('agent.rapport', $record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('verifier_agrements')
                        ->icon('heroicon-o-shield-check')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->verifierAgrement();
                            }
                        }),
                ]),
            ])
            ->defaultSort('statut_agent')
            ->striped()
            ->deferLoading();
    }
}
