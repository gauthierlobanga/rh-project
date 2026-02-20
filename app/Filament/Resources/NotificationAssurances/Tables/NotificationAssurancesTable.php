<?php

namespace App\Filament\Resources\NotificationAssurances\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NotificationAssurancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('destinataire.name')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),

                TextColumn::make('type_notification')
                    ->badge()
                    ->color(fn ($record) => $record->couleur)
                    ->sortable()
                    ->icon('heroicon-o-tag'),

                TextColumn::make('titre')
                    ->searchable()
                    ->limit(50)
                    ->icon('heroicon-o-chat-bubble-left'),

                IconColumn::make('est_lue')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash'),

                IconColumn::make('est_envoyee')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-airplane')
                    ->falseIcon('heroicon-o-clock'),

                TextColumn::make('canal_envoi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'email' => 'blue',
                        'sms' => 'green',
                        'application' => 'purple',
                        'tous' => 'orange',
                        default => 'gray',
                    })
                    ->icon('heroicon-o-megaphone'),

                IconColumn::make('est_urgente')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-clock'),

                TextColumn::make('date_envoi')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-calendar'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type_notification')
                    ->options([
                        'rappel_paiement' => 'Rappel de paiement',
                        'echeance_proche' => 'Échéance proche',
                        'sinistre_declare' => 'Sinistre déclaré',
                        'alerte_securite' => 'Alerte sécurité',
                        'contrat_active' => 'Contrat activé',
                        'commission_calculee' => 'Commission calculée',
                        'information' => 'Information',
                        'promotion' => 'Promotion',
                        'systeme' => 'Système',
                    ]),

                SelectFilter::make('canal_envoi')
                    ->options([
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'application' => 'Application',
                        'tous' => 'Tous les canaux',
                    ]),

                TernaryFilter::make('est_lue')
                    ->label('Lue'),

                TernaryFilter::make('est_envoyee')
                    ->label('Envoyée'),

                TernaryFilter::make('est_urgente')
                    ->label('Urgente'),

                Filter::make('date_envoi')
                    ->schema([
                        DatePicker::make('envoi_from'),
                        DatePicker::make('envoi_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['envoi_from'], fn ($q, $date) => $q->whereDate('date_envoi', '>=', $date))
                            ->when($data['envoi_until'], fn ($q, $date) => $q->whereDate('date_envoi', '<=', $date));
                    }),

                Filter::make('expirees')
                    ->label('Expirées')
                    ->query(fn ($query) => $query->whereNotNull('date_expiration')
                        ->where('date_expiration', '<', now())
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('marquer_lue')
                    ->icon('heroicon-o-eye')
                    ->action(fn ($record) => $record->marquerCommeLue())
                    ->hidden(fn ($record) => $record->est_lue),

                Action::make('envoyer')
                    ->icon('heroicon-o-paper-airplane')
                    ->action(fn ($record) => $record->envoyer())
                    ->hidden(fn ($record) => $record->est_envoyee),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('marquer_lues')
                        ->icon('heroicon-o-eye')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->marquerCommeLue();
                            }
                        }),
                    BulkAction::make('envoyer_toutes')
                        ->icon('heroicon-o-paper-airplane')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (! $record->est_envoyee) {
                                    $record->envoyer();
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
