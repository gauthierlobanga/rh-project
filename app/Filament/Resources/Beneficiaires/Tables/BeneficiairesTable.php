<?php

namespace App\Filament\Resources\Beneficiaires\Tables;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\ContratAssuranceVies\ContratAssuranceVieResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BeneficiairesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom_complet')
                    ->searchable(['nom', 'prenom'])
                    ->sortable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('contrat.numero_contrat')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => ContratAssuranceVieResource::getUrl('view', ['record' => $record->contrat_id])),

                TextColumn::make('contrat.souscripteur.nom_complet')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => ClientResource::getUrl('view', ['record' => $record->contrat->souscripteur_id])),

                TextColumn::make('lien_parente')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user-group'),

                TextColumn::make('pourcentage_attribution')
                    ->suffix('%')
                    ->sortable()
                    ->alignment('right')
                    ->icon('heroicon-o-percent-badge'),

                IconColumn::make('est_beneficiaire_primaire')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star'),

                TextColumn::make('statut_beneficiaire')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'actif' => 'success',
                        'inactif' => 'warning',
                        'decede' => 'danger',
                        'inconnu' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('age')
                    ->numeric()
                    ->suffix(' ans')
                    ->sortable()
                    ->icon('heroicon-o-clock'),

                TextColumn::make('montant_attribue')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right')
                    ->icon('heroicon-o-currency-euro'),
            ])
            ->filters([
                SelectFilter::make('contrat_id')
                    ->relationship('contrat', 'numero_contrat')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('statut_beneficiaire')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                        'decede' => 'Décédé',
                        'inconnu' => 'Inconnu',
                    ]),

                TernaryFilter::make('est_beneficiaire_primaire')
                    ->label('Bénéficiaire primaire'),

                Filter::make('date_naissance')
                    ->schema([
                        DatePicker::make('naissance_from'),
                        DatePicker::make('naissance_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['naissance_from'], fn ($q, $date) => $q->whereDate('date_naissance', '>=', $date))
                            ->when($data['naissance_until'], fn ($q, $date) => $q->whereDate('date_naissance', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('modifier_pourcentage')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        TextInput::make('nouveau_pourcentage')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->label('Nouveau pourcentage')
                            ->suffix('%'),
                    ])
                    ->action(function ($record, array $data) {
                        $success = $record->mettreAJourPourcentage($data['nouveau_pourcentage']);
                        if (! $success) {
                            Notification::make()
                                ->title('Erreur')
                                ->body('Le total des pourcentages ne peut pas dépasser 100%')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('contrat_id')
            ->striped();
    }
}
