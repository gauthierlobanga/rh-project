<?php

namespace App\Filament\Resources\ProduitAssurances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class ProduitAssurancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code_produit')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-hashtag'),

                Tables\Columns\TextColumn::make('nom_produit')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-tag'),

                Tables\Columns\TextColumn::make('categorie')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'vie_entier' => 'success',
                        'temporaire' => 'warning',
                        'mixte' => 'info',
                        'epargne' => 'primary',
                        'retraite' => 'danger',
                        'capitalisation' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('est_actif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('capital_minimum')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('capital_maximum')
                    ->money('EUR')
                    ->sortable()
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('contrats_count')
                    ->counts('contrats')
                    ->label('Contrats')
                    ->icon('heroicon-o-document-text'),

                Tables\Columns\TextColumn::make('prime_moyenne')
                    ->state(fn ($record) => $record->prime_moyenne)
                    ->money('EUR')
                    ->alignment('right'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categorie')
                    ->options([
                        'vie_entier' => 'Vie entière',
                        'temporaire' => 'Temporaire',
                        'mixte' => 'Mixte',
                        'epargne' => 'Épargne',
                        'retraite' => 'Retraite',
                        'capitalisation' => 'Capitalisation',
                    ]),

                Tables\Filters\TernaryFilter::make('est_actif')
                    ->label('Actif'),

                Tables\Filters\Filter::make('date_activation')
                    ->form([
                        Forms\Components\DatePicker::make('activated_from')
                            ->label('Activé après'),
                        Forms\Components\DatePicker::make('activated_until')
                            ->label('Activé avant'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['activated_from'], fn ($q, $date) => $q->whereDate('date_activation', '>=', $date))
                            ->when($data['activated_until'], fn ($q, $date) => $q->whereDate('date_activation', '<=', $date));
                    }),
            ])
            ->recordActions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('simuler_prime')
                    ->icon('heroicon-o-calculator')
                    ->form([
                        Forms\Components\TextInput::make('capital')
                            ->numeric()
                            ->required()
                            ->label('Capital assuré')
                            ->prefix('€'),
                        Forms\Components\TextInput::make('age')
                            ->numeric()
                            ->required()
                            ->label('Âge du souscripteur')
                            ->suffix('ans'),
                        Forms\Components\TextInput::make('duree')
                            ->numeric()
                            ->required()
                            ->label('Durée du contrat')
                            ->suffix('ans'),
                    ])
                    ->action(function ($record, array $data) {
                        $prime = $record->calculerPrimeTheorique(
                            $data['capital'],
                            $data['age'],
                            $data['duree']
                        );

                        Notification::make()
                            ->title('Simulation de prime')
                            ->body("Prime théorique: " . number_format($prime, 2, ',', ' ') . " €")
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activer')
                        ->icon('heroicon-o-check-circle')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['est_actif' => true]);
                            }
                        }),
                    Tables\Actions\BulkAction::make('desactiver')
                        ->icon('heroicon-o-x-circle')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['est_actif' => false]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('nom_produit')
            ->striped();

    }
}
