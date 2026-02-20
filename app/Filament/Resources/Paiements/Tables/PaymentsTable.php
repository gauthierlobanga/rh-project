<?php

namespace App\Filament\Resources\Paiements\Tables;

use App\Filament\Resources\ContratAssuranceVies\ContratAssuranceVieResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type_paiement')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cotisation' => 'success',
                        'indemnisation' => 'warning',
                        'rachat' => 'info',
                        'frais' => 'gray',
                        'remboursement' => 'primary',
                        'autre' => 'gray',
                        default => 'gray',
                    })
                    ->sortable()
                    ->icon('heroicon-o-tag'),

                TextColumn::make('contrat.numero_contrat')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->contrat_id ?
                        ContratAssuranceVieResource::getUrl('view', ['record' => $record->contrat_id]) :
                        null
                    ),

                TextColumn::make('montant_paiement')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right')
                    ->icon('heroicon-o-currency-euro'),

                TextColumn::make('date_paiement')
                    ->date()
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('statut_paiement')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'valide' => 'success',
                        'en_cours' => 'warning',
                        'refuse' => 'danger',
                        'annule' => 'gray',
                        'en_attente' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('mode_paiement')
                    ->badge()
                    ->color('blue')
                    ->sortable()
                    ->icon('heroicon-o-credit-card'),

                TextColumn::make('reference_paiement')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-hashtag'),

                IconColumn::make('est_recurrent')
                    ->boolean()
                    ->label('Recurrent')
                    ->trueIcon('heroicon-o-arrow-path')
                    ->falseIcon('heroicon-o-clock'),

                TextColumn::make('validateur.name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user-check'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type_paiement')
                    ->options([
                        'cotisation' => 'Cotisation',
                        'indemnisation' => 'Indemnisation',
                        'rachat' => 'Rachat',
                        'frais' => 'Frais',
                        'remboursement' => 'Remboursement',
                        'autre' => 'Autre',
                    ]),

                SelectFilter::make('statut_paiement')
                    ->options([
                        'en_cours' => 'En cours',
                        'valide' => 'Validé',
                        'refuse' => 'Refusé',
                        'annule' => 'Annulé',
                        'en_attente' => 'En attente',
                    ]),

                SelectFilter::make('mode_paiement')
                    ->options([
                        'virement' => 'Virement',
                        'cheque' => 'Chèque',
                        'carte' => 'Carte bancaire',
                        'prelevement' => 'Prélèvement',
                        'especes' => 'Espèces',
                        'compensation' => 'Compensation',
                    ]),

                TernaryFilter::make('est_recurrent')
                    ->label('Récurrent'),

                Filter::make('date_paiement')
                    ->schema([
                        DatePicker::make('paiement_from'),
                        DatePicker::make('paiement_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['paiement_from'], fn ($q, $date) => $q->whereDate('date_paiement', '>=', $date))
                            ->when($data['paiement_until'], fn ($q, $date) => $q->whereDate('date_paiement', '<=', $date));
                    }),

                Filter::make('montant_paiement')
                    ->schema([
                        TextInput::make('montant_min')
                            ->numeric()
                            ->label('Montant minimum')
                            ->prefix('€'),
                        TextInput::make('montant_max')
                            ->numeric()
                            ->label('Montant maximum')
                            ->prefix('€'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['montant_min'], fn ($q, $montant) => $q->where('montant_paiement', '>=', $montant))
                            ->when($data['montant_max'], fn ($q, $montant) => $q->where('montant_paiement', '<=', $montant));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ActionGroup::make([
                    Action::make('valider')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($record) {
                            $record->valider(auth()->user());
                        })
                        ->hidden(fn ($record) => $record->est_valide),

                    Action::make('refuser')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->schema([
                            Textarea::make('motif')
                                ->required()
                                ->label('Motif du refus'),
                        ])
                        ->action(function ($record, array $data) {
                            $record->refuser($data['motif']);
                        })
                        ->hidden(fn ($record) => $record->est_refuse),

                    Action::make('generer_releve')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function ($record) {
                            $releve = $record->genererReleve();
                            // Logique pour générer le PDF
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('valider_tous')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (! $record->est_valide) {
                                    $record->valider(auth()->user());
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('date_paiement', 'desc')
            ->striped()
            ->deferLoading();
    }
}
