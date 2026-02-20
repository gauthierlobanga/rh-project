<?php

namespace App\Filament\Resources\Commissions\Tables;

use App\Filament\Resources\Agents\AgentResource;
use App\Filament\Resources\ContratAssuranceVies\ContratAssuranceVieResource;
use App\Models\Commission;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CommissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('agent.nom_complet')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => AgentResource::getUrl('view', ['record' => $record->agent_id])),

                TextColumn::make('contrat.numero_contrat')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => ContratAssuranceVieResource::getUrl('view', ['record' => $record->contrat_id])),

                TextColumn::make('type_commission')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'acquisition' => 'success',
                        'renouvellement' => 'info',
                        'performance' => 'warning',
                        'service' => 'primary',
                        'loyalty' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('statut_commission')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'payee' => 'success',
                        'a_payer' => 'warning',
                        'calculee' => 'info',
                        'annulee' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('montant_prime')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right')
                    ->icon('heroicon-o-banknotes'),

                TextColumn::make('taux_commission')
                    ->suffix('%')
                    ->sortable()
                    ->alignment('right')
                    ->icon('heroicon-o-percent-badge'),

                TextColumn::make('montant_commission')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right')
                    ->icon('heroicon-o-currency-dollar'),

                TextColumn::make('montant_net')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right')
                    ->icon('heroicon-o-banknotes'),

                TextColumn::make('date_calcul')
                    ->date()
                    ->sortable()
                    ->icon('heroicon-o-calculator'),

                TextColumn::make('date_paiement')
                    ->date()
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('annee_comptable')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('agent_id')
                    ->relationship('agent', 'nom_complet')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('statut_commission')
                    ->options([
                        'calculee' => 'Calculée',
                        'a_payer' => 'À payer',
                        'payee' => 'Payée',
                        'annulee' => 'Annulée',
                    ]),

                SelectFilter::make('type_commission')
                    ->options([
                        'acquisition' => 'Acquisition',
                        'renouvellement' => 'Renouvellement',
                        'performance' => 'Performance',
                        'service' => 'Service',
                        'loyalty' => 'Fidélité',
                    ]),

                Filter::make('date_calcul')
                    ->schema([
                        DatePicker::make('calcul_from'),
                        DatePicker::make('calcul_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['calcul_from'], fn ($q, $date) => $q->whereDate('date_calcul', '>=', $date))
                            ->when($data['calcul_until'], fn ($q, $date) => $q->whereDate('date_calcul', '<=', $date));
                    }),

                SelectFilter::make('annee_comptable')
                    ->options(fn () => Commission::distinct('annee_comptable')
                        ->pluck('annee_comptable', 'annee_comptable')
                        ->sortDesc()
                        ->toArray()
                    ),

                Filter::make('a_payer')
                    ->label('À payer')
                    ->query(fn ($query) => $query->where('statut_commission', 'a_payer')),
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
                        TextInput::make('numero_paiement')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->action(function ($record, array $data) {
                        $record->marquerCommePayee($data['numero_paiement']);
                    })
                    ->hidden(fn ($record) => $record->est_payee),

                Action::make('generer_fiche_paie')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function ($record) {
                        $fiche = $record->genererFichePaie();
                        // Logique pour générer le PDF
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('marquer_payees')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->marquerCommePayee('PAY-'.date('Ymd-His'));
                            }
                        }),
                ]),
            ])
            ->defaultSort('date_calcul', 'desc')
            ->striped()
            ->deferLoading();
    }
}
