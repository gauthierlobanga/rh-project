<?php

namespace App\Filament\Resources\Sinistres\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SinistresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_sinistre')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-hashtag'),

                TextColumn::make('contrat.numero_contrat')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => ContratAssuranceVieResource::getUrl('view', ['record' => $record->contrat_id])),

                TextColumn::make('contrat.souscripteur.nom_complet')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => ClientResource::getUrl('view', ['record' => $record->contrat->souscripteur_id])),

                TextColumn::make('type_sinistre')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'deces' => 'danger',
                        'invalidite' => 'warning',
                        'incapacite' => 'warning',
                        'rachat' => 'info',
                        'resiliation' => 'gray',
                        'autre' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('statut_sinistre')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'declare' => 'info',
                        'en_cours_examen' => 'warning',
                        'documents_manquants' => 'warning',
                        'expertise_en_cours' => 'warning',
                        'accepte' => 'success',
                        'refuse' => 'danger',
                        'indemnise' => 'success',
                        'cloture' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('date_survenance')
                    ->date()
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('montant_reclame')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right')
                    ->icon('heroicon-o-currency-euro'),

                TextColumn::make('montant_accordee')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right')
                    ->icon('heroicon-o-check-circle'),

                TextColumn::make('expert.nom_complet')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user-circle'),

                IconColumn::make('est_fraude_suspectee')
                    ->boolean()
                    ->label('Fraude')
                    ->trueIcon('heroicon-o-shield-exclamation')
                    ->falseIcon('heroicon-o-shield-check'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type_sinistre')
                    ->options([
                        'deces' => 'Décès',
                        'invalidite' => 'Invalidité',
                        'incapacite' => 'Incapacité',
                        'rachat' => 'Rachat',
                        'resiliation' => 'Résiliation',
                        'autre' => 'Autre',
                    ]),

                SelectFilter::make('statut_sinistre')
                    ->options([
                        'declare' => 'Déclaré',
                        'en_cours_examen' => 'En cours d\'examen',
                        'documents_manquants' => 'Documents manquants',
                        'expertise_en_cours' => 'Expertise en cours',
                        'accepte' => 'Accepté',
                        'refuse' => 'Refusé',
                        'indemnise' => 'Indemnisé',
                        'cloture' => 'Clôturé',
                    ]),

                SelectFilter::make('expert_id')
                    ->relationship('expert', 'nom_complet')
                    ->searchable()
                    ->preload(),

                TernaryFilter::make('est_fraude_suspectee')
                    ->label('Fraude suspectée'),

                Filter::make('date_survenance')
                    ->form([
                        DatePicker::make('survenance_from'),
                        DatePicker::make('survenance_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['survenance_from'], fn ($q, $date) => $q->whereDate('date_survenance', '>=', $date))
                            ->when($data['survenance_until'], fn ($q, $date) => $q->whereDate('date_survenance', '<=', $date));
                    }),

                Filter::make('en_cours')
                    ->label('En cours de traitement')
                    ->query(fn ($query) => $query->whereIn('statut_sinistre', [
                        'declare', 'en_cours_examen', 'documents_manquants', 'expertise_en_cours'
                    ])),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ActionGroup::make([
                    Action::make('assigner_expert')
                        ->icon('heroicon-o-user-circle')
                        ->form([
                            Select::make('expert_id')
                                ->relationship('expert', 'nom_complet')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $expert = \App\Models\Agent::find($data['expert_id']);
                            $record->assignerExpert($expert);
                        })
                        ->hidden(fn ($record) => $record->expert_id !== null),

                    Action::make('demander_documents')
                        ->icon('heroicon-o-document-plus')
                        ->form([
                            KeyValue::make('documents')
                                ->keyLabel('Document')
                                ->valueLabel('Date limite')
                                ->addable(true)
                                ->deletable(true)
                                ->editableKeys(true)
                                ->editableValues(true),
                        ])
                        ->action(function ($record, array $data) {
                            $record->demanderDocuments(array_keys($data['documents'] ?? []));
                        }),

                    Action::make('accepter')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->form([
                            TextInput::make('montant_accorde')
                                ->numeric()
                                ->required()
                                ->label('Montant accordé')
                                ->prefix('€'),
                            KeyValue::make('beneficiaires')
                                ->keyLabel('Bénéficiaire')
                                ->valueLabel('Pourcentage')
                                ->required()
                                ->addable(true)
                                ->deletable(true)
                                ->editableKeys(true)
                                ->editableValues(true),
                        ])
                        ->action(function ($record, array $data) {
                            $record->accepter($data['montant_accorde'], $data['beneficiaires']);
                        })
                        ->hidden(fn ($record) => in_array($record->statut_sinistre, ['accepte', 'refuse', 'indemnise', 'cloture'])),

                    Action::make('refuser')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            Textarea::make('motif')
                                ->required()
                                ->label('Motif du refus'),
                        ])
                        ->action(function ($record, array $data) {
                            $record->refuser($data['motif']);
                        })
                        ->hidden(fn ($record) => in_array($record->statut_sinistre, ['accepte', 'refuse', 'indemnise', 'cloture'])),

                    Action::make('indemniser')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->requiresConfirmation()
                        ->form([
                            TextInput::make('numero_virement')
                                ->required()
                                ->label('Numéro de virement')
                                ->maxLength(255),
                        ])
                        ->action(function ($record, array $data) {
                            $record->indemniser($data['numero_virement']);
                        })
                        ->hidden(fn ($record) => $record->statut_sinistre !== 'accepte'),

                    Action::make('cloturer')
                        ->icon('heroicon-o-lock-closed')
                        ->requiresConfirmation()
                        ->action(fn ($record) => $record->cloturer())
                        ->hidden(fn ($record) => $record->statut_sinistre === 'cloture'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('assigner_expert')
                        ->icon('heroicon-o-user-circle')
                        ->form([
                            Select::make('expert_id')
                                ->relationship('expert', 'nom_complet')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $expert = \App\Models\Agent::find($data['expert_id']);
                            foreach ($records as $record) {
                                if (!$record->expert_id) {
                                    $record->assignerExpert($expert);
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('date_survenance', 'desc')
            ->striped();
    }
}
