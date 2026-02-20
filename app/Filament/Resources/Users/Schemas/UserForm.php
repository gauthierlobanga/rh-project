<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                Section::make('Sécurité et authentification')
                    ->description('Paramètres de connexion et de sécurité')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->confirmed()
                            ->dehydrated(fn($state) => filled($state))
                            ->helperText('Minimum 8 caractères')
                            ->required(fn(string $context): bool => $context === 'create'),

                        TextInput::make('password_confirmation')
                            ->label('Confirmation du mot de passe')
                            ->password()
                            ->revealable()
                            ->same('password')
                            ->dehydrated(false)
                            ->required(fn(string $context): bool => $context === 'create'),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email vérifié le')
                            ->displayFormat('d/m/Y H:i')
                            ->native(false)
                            ->seconds(false),
                    ])
                    ->columns(2),

                Section::make('Rôles et permissions')
                    ->description('Gestion des accès et autorisations')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Select::make('roles')
                            ->label('Rôles attribués')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->live(onBlur: true)
                            ->searchable()
                            ->options(Role::pluck('name', 'id'))
                            ->helperText('Sélectionnez un ou plusieurs rôles'),
                    ]),

                Section::make('Informations système')
                    ->description('Métadonnées et informations techniques')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('created_at')
                            ->label('Créé le')
                            ->disabled()
                            ->formatStateUsing(fn($record): ?string => $record?->created_at?->format('d/m/Y H:i') ?? 'N/A'),

                        TextInput::make('updated_at')
                            ->label('Modifié le')
                            ->disabled()
                            ->formatStateUsing(fn($record): ?string => $record?->updated_at?->format('d/m/Y H:i') ?? 'N/A'),

                        TextInput::make('last_login')
                            ->label('Dernière connexion')
                            ->disabled()
                            ->formatStateUsing(fn($record): ?string => 'À implémenter selon vos besoins'),
                    ])
                    ->columns(3)
                    ->visible(fn($record) => $record !== null),
                Textarea::make('two_factor_secret')
                    ->columnSpanFull(),
                Textarea::make('two_factor_recovery_codes')
                    ->columnSpanFull(),
                DateTimePicker::make('two_factor_confirmed_at'),
            ]);
    }
}
