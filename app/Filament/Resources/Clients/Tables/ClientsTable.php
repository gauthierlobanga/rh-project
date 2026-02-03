<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference_client')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                // TextColumn::make('utilisateur.name')
                //     ->searchable()
                //     ->sortable()
                //     ->description(fn ($record) => $record->email),

                TextColumn::make('telephone_mobile')
                    ->searchable()
                    ->icon('heroicon-o-phone'),

                TextColumn::make('type_client')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'particulier' => 'info',
                        'professionnel' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('kyc_verifie')
                    ->boolean()
                    ->label('KYC')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                // TextColumn::make('agent.utilisateur.name')
                //     ->searchable()
                //     ->sortable()
                //     ->icon('heroicon-o-user-circle'),

                TextColumn::make('contrats_count')
                    ->counts('contrats')
                    ->label('Contrats')
                    ->icon('heroicon-o-document-text'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type_client')
                    ->options([
                        'particulier' => 'Particulier',
                        'professionnel' => 'Professionnel',
                    ]),

                TernaryFilter::make('kyc_verifie')
                    ->label('KYC Vérifié'),

                // SelectFilter::make('agent_id')
                //     ->relationship('agent', 'utilisateur.name')
                //     ->searchable()
                //     ->preload(),

                Filter::make('has_contrats')
                    ->label('Avec contrats actifs')
                    ->query(fn ($query) => $query->whereHas('contrats', fn ($q) => $q->where('statut_contrat', 'actif')
                    )),

                Filter::make('date_naissance')
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('Né après'),
                        DatePicker::make('date_until')
                            ->label('Né avant'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['date_from'], fn ($q, $date) => $q->whereDate('date_naissance', '>=', $date))
                            ->when($data['date_until'], fn ($q, $date) => $q->whereDate('date_naissance', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('verifier_kyc')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->verifierKyc())
                    ->hidden(fn ($record) => $record->kyc_verifie),

                Action::make('calculer_score')
                    ->icon('heroicon-o-chart-bar')
                    ->action(function ($record) {
                        $score = $record->calculerScoreRisque();
                        Notification::make()
                            ->title('Score de risque calculé')
                            ->body("Score: {$score}/100")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('verifier_kyc')
                        ->icon('heroicon-o-check-circle')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->verifierKyc();
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped();
    }
}
