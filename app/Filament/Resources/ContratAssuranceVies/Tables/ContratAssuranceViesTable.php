<?php

namespace App\Filament\Resources\ContratAssuranceVies\Tables;

use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ContratAssuranceViesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_contrat')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('souscripteur.utilisateur.name')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => ClientResource::getUrl('view', ['record' => $record->souscripteur_id])),

                TextColumn::make('produit.nom_produit')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('capital_assure')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right'),

                TextColumn::make('prime_annuelle')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right'),

                TextColumn::make('statut_contrat')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'en_attente' => 'warning',
                        'suspendu' => 'danger',
                        'resilie' => 'gray',
                        'expire' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('date_effet')
                    ->date()
                    ->sortable(),

                TextColumn::make('date_echeance')
                    ->date()
                    ->sortable()
                    ->color(fn ($record) => $record->date_echeance?->isPast() ? 'danger' : null),

                TextColumn::make('agent.utilisateur.name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('generer_quittance')
                    ->icon('heroicon-o-document-arrow-down')
                    ->url(fn ($record) => route('contrat.quittance', $record))
                    ->openUrlInNewTab(),

                ActionGroup::make([
                    Action::make('resilier')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->schema([
                            Textarea::make('motif_resiliation')
                                ->required()
                                ->label('Motif de résiliation'),
                        ])
                        ->action(function ($record, array $data) {
                            $record->resiliation($data['motif_resiliation']);
                        })
                        ->hidden(fn ($record) => $record->statut_contrat === 'resilie'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    BulkAction::make('changer_statut')
                        ->icon('heroicon-o-arrows-right-left')
                        ->schema([
                            Select::make('statut')
                                ->options([
                                    'actif' => 'Actif',
                                    'suspendu' => 'Suspendu',
                                    'resilie' => 'Résilié',
                                ])
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $record) {
                                $record->update(['statut_contrat' => $data['statut']]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('date_effet', 'desc')
            ->striped()
            ->deferLoading();
    }
}
