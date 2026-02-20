<?php

namespace App\Filament\Resources\Cotisations\Tables;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\ContratAssuranceVies\ContratAssuranceVieResource;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CotisationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contrat.numero_contrat')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => ContratAssuranceVieResource::getUrl('view', ['record' => $record->contrat_id])),

                TextColumn::make('contrat.souscripteur.nom_complet')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => ClientResource::getUrl('view', ['record' => $record->contrat->souscripteur_id])),

                TextColumn::make('date_echeance')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) =>
                    $record->statut_paiement === 'en_retard' ? 'danger' :
                        ($record->date_echeance?->isPast() && $record->statut_paiement !== 'paye' ? 'warning' : null)
                    )
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('montant_due')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right')
                    ->icon('heroicon-o-currency-euro'),

                TextColumn::make('montant_paye')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right')
                    ->icon('heroicon-o-banknotes'),

                TextColumn::make('statut_paiement')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paye' => 'success',
                        'en_retard' => 'danger',
                        'partiellement_paye' => 'warning',
                        'en_attente' => 'info',
                        'annule' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('montant_restant')
                    ->state(fn ($record) => $record->montant_restant)
                    ->money('EUR')
                    ->alignment('right')
                    ->icon('heroicon-o-exclamation-circle'),

                IconColumn::make('est_rappele')
                    ->boolean()
                    ->label('Relancé')
                    ->trueIcon('heroicon-o-bell-alert')
                    ->falseIcon('heroicon-o-bell'),

                TextColumn::make('date_paiement')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('statut_paiement')
                    ->options([
                        'en_attente' => 'En attente',
                        'paye' => 'Payé',
                        'en_retard' => 'En retard',
                        'partiellement_paye' => 'Partiellement payé',
                        'annule' => 'Annulé',
                    ]),

                SelectFilter::make('contrat_id')
                    ->relationship('contrat', 'numero_contrat')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('est_rappele')
                    ->label('Relancé'),

                Filter::make('date_echeance')
                    ->schema([
                       DatePicker::make('echeance_from'),
                       DatePicker::make('echeance_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['echeance_from'], fn ($q, $date) => $q->whereDate('date_echeance', '>=', $date))
                            ->when($data['echeance_until'], fn ($q, $date) => $q->whereDate('date_echeance', '<=', $date));
                    }),

                Filter::make('en_retard')
                    ->label('En retard de paiement')
                    ->query(fn ($query) => $query->where('statut_paiement', 'en_retard')),

                Filter::make('a_relancer')
                    ->label('À relancer')
                    ->query(fn ($query) => $query->where('est_rappele', false)
                        ->where('statut_paiement', '!=', 'paye')
                        ->where('date_echeance', '<=', now())
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('marquer_payee')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->schema([
                       DatePicker::make('date_paiement')
                            ->default(today())
                            ->native(false)
                            ->required(),
                       Select::make('mode_paiement')
                            ->options([
                                'prelevement' => 'Prélèvement',
                                'virement' => 'Virement',
                                'cheque' => 'Chèque',
                                'especes' => 'Espèces',
                                'carte' => 'Carte bancaire',
                            ])
                            ->required(),
                       TextInput::make('reference_paiement')
                            ->maxLength(255),
                    ])
                    ->action(function ($record, array $data) {
                        $record->marquerCommePayee([
                            'mode_paiement' => $data['mode_paiement'],
                            'reference' => $data['reference_paiement'],
                            'date' => $data['date_paiement'],
                        ]);
                    })
                    ->hidden(fn ($record) => $record->est_payee),

                Action::make('envoyer_rappel')
                    ->icon('heroicon-o-bell-alert')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->envoyerRappel())
                    ->hidden(fn ($record) => $record->est_rappele || $record->est_payee),

                Action::make('generer_facture')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn ($record) => route('cotisation.facture', $record))
                    ->openUrlInNewTab(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('marquer_payees')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->marquerCommePayee([
                                    'mode_paiement' => 'virement',
                                    'reference' => 'Paiement groupé',
                                    'date' => today(),
                                ]);
                            }
                        }),
                    BulkAction::make('envoyer_relances')
                        ->icon('heroicon-o-bell-alert')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (!$record->est_rappele && !$record->est_payee) {
                                    $record->envoyerRappel();
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('date_echeance')
            ->striped();
    }
}
