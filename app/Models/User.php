<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Détermine si l'utilisateur peut accéder à un panel Filament.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            // À ajuster selon votre logique (rôles, etc.)
            return str_ends_with($this->email, '@admin.com') || $this->hasRole('admin');
        }

        return true;
    }

    // =========================================================================
    // Relations pour le module d'assurance vie
    // =========================================================================

    /**
     * Un utilisateur peut être associé à un client (souscripteur).
     */
    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'user_id');
    }

    /**
     * Un utilisateur peut être associé à un agent (si c'est un agent).
     */
    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class, 'user_id');
    }

    /**
     * Paiements validés par cet utilisateur.
     */
    public function paiementsValides(): HasMany
    {
        return $this->hasMany(Paiement::class, 'valide_par');
    }

    /**
     * Réserves actuarielles validées par cet utilisateur.
     */
    public function reservesValidees(): HasMany
    {
        return $this->hasMany(ReserveActuarielle::class, 'valide_par');
    }

    /**
     * Achats/rachats validés par cet utilisateur.
     */
    public function achatsRachatsValides(): HasMany
    {
        return $this->hasMany(AchatRachat::class, 'valide_par');
    }

    /**
     * Notifications destinées à cet utilisateur.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(NotificationAssurance::class, 'destinataire_id');
    }

    /**
     * Imports de fichiers effectués par cet utilisateur.
     */
    // public function imports(): HasMany
    // {
    //     return $this->hasMany(FichierImport::class, 'importe_par');
    // }

    /**
     * Historique des modifications des contrats (relation polymorphique).
     */
    public function historiqueContrats(): MorphMany
    {
        return $this->morphMany(HistoriqueContrat::class, 'utilisateur');
    }

    // =========================================================================
    // Relations existantes (exemple emprunts)
    // =========================================================================

    /**
     * Relation vers les emprunts (si applicable).
     */
    public function emprunts()
    {
        return $this->hasMany(Emprunt::class);
    }
}
