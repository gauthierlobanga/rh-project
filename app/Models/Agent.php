<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    protected $table = 'agents';

    protected $fillable = [
        'user_id ',
        'matricule_agent',
        'numero_agrement',
        'date_expiration_agrement',
        'statut_agent',
        'taux_commission',
        'coordonnees_professionnelles',
        'specialisations',
        'objectif_annuel',
        'performance_annuelle',
        'agence_affectation',
    ];

    protected $casts = [
        'date_expiration_agrement' => 'date',
        'coordonnees_professionnelles' => 'array',
        'specialisations' => 'array',
        'taux_commission' => 'decimal:2',
        'objectif_annuel' => 'decimal:2',
        'performance_annuelle' => 'decimal:2',
    ];

    // Relations
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id ');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'agent_id');
    }

    public function contrats(): HasMany
    {
        return $this->hasMany(ContratAssuranceVie::class, 'agent_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'agent_id');
    }

    public function sinistres(): HasMany
    {
        return $this->hasMany(Sinistre::class, 'expert_id');
    }

    // Accesseurs
    public function getNomCompletAttribute(): string
    {
        return $this->utilisateur->name;
    }

    public function getEmailAttribute(): string
    {
        return $this->utilisateur->email;
    }

    public function getEstActifAttribute(): bool
    {
        return $this->statut_agent === 'actif';
    }

    public function getAgrementValideAttribute(): bool
    {
        return $this->date_expiration_agrement && $this->date_expiration_agrement > now();
    }

    public function getTotalCommissionsAttribute(): float
    {
        return $this->commissions()->where('statut_commission', 'payee')->sum('montant_commission');
    }

    public function getCommissionsEnAttenteAttribute(): float
    {
        return $this->commissions()->where('statut_commission', 'a_payer')->sum('montant_commission');
    }

    public function getNombreContratsAttribute(): int
    {
        return $this->contrats()->count();
    }

    public function getValeurPortefeuilleAttribute(): float
    {
        return $this->contrats()->sum('capital_assure');
    }

    public function getTauxConversionAttribute(): ?float
    {
        $prospects = $this->clients()->count();
        $contrats = $this->contrats()->count();

        if ($prospects > 0) {
            return ($contrats / $prospects) * 100;
        }

        return null;
    }

    // Scopes
    public function scopeActifs($query)
    {
        return $query->where('statut_agent', 'actif');
    }

    public function scopeAvecAgrementValide($query)
    {
        return $query->where('date_expiration_agrement', '>', now());
    }

    public function scopeParAgence($query, $agence)
    {
        return $query->where('agence_affectation', $agence);
    }

    // Méthodes métier
    public function calculerPerformance(): void
    {
        $objectif = $this->objectif_annuel ?? 0;
        $realise = $this->contrats()->whereYear('created_at', now()->year)->sum('prime_annuelle');

        if ($objectif > 0) {
            $performance = ($realise / $objectif) * 100;
            $this->update(['performance_annuelle' => $performance]);
        }
    }

    public function verifierAgrement(): bool
    {
        if (! $this->date_expiration_agrement || $this->date_expiration_agrement < now()) {
            $this->update(['statut_agent' => 'inactif']);

            return false;
        }

        return true;
    }

    public function genererRapportActivite($debut, $fin): array
    {
        return [
            'period' => ['debut' => $debut, 'fin' => $fin],
            'contrats_souscrits' => $this->contrats()->whereBetween('created_at', [$debut, $fin])->count(),
            'primes_total' => $this->contrats()->whereBetween('created_at', [$debut, $fin])->sum('prime_annuelle'),
            'commissions' => $this->commissions()->whereBetween('created_at', [$debut, $fin])->sum('montant_commission'),
            'clients_nouveaux' => $this->clients()->whereBetween('created_at', [$debut, $fin])->count(),
            'sinistres_traites' => $this->sinistres()->whereBetween('created_at', [$debut, $fin])->count(),
        ];
    }
}
