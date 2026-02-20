<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Client extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'clients';

    protected $fillable = [
        'user_id',
        'reference_client',
        'type_client',
        'civilite',
        'nom',
        'prenom',
        'date_naissance',
        'lieu_naissance',
        'nationalite',
        'profession',
        'numero_cni',
        'date_expiration_cni',
        'email',
        'telephone_fixe',
        'telephone_mobile',
        'adresse',
        'coordonnees_bancaires',
        'situation_familiale',
        'nombre_enfants',
        'revenu_annuel',
        'profil_risque',
        'kyc_verifie',
        'date_verification_kyc',
        'notes',
        'agent_id',
        'source_acquisition',
    ];

    protected function casts(): array
    {
        return [
            'date_naissance' => 'date',
            'date_expiration_cni' => 'date',
            'date_verification_kyc' => 'date',
            'adresse' => 'array',
            'coordonnees_bancaires' => 'array',
            'profil_risque' => 'array',
            'revenu_annuel' => 'decimal:2',
            'kyc_verifie' => 'boolean',
        ];
    }

    // Relations
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function contrats(): HasMany
    {
        return $this->hasMany(ContratAssuranceVie::class, 'souscripteur_id');
    }

    public function beneficiaires(): HasMany
    {
        return $this->hasMany(Beneficiaire::class, 'client_id');
    }

    public function cotisations()
    {
        return $this->hasManyThrough(Cotisation::class, ContratAssuranceVie::class, 'souscripteur_id', 'contrat_id');
    }

    public function sinistres()
    {
        return $this->hasManyThrough(Sinistre::class, ContratAssuranceVie::class, 'souscripteur_id', 'contrat_id');
    }

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('pieces_identite')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf'])
            ->withResponsiveImages()
            ->singleFile();

        $this->addMediaCollection('justificatifs_domicile')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);

        $this->addMediaCollection('documents_bancaires')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf']);

        $this->addMediaCollection('autres_documents')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'application/pdf', 'application/msword']);
    }

    // Accesseurs
    public function getNomCompletAttribute(): string
    {
        return trim($this->civilite.' '.$this->prenom.' '.$this->nom);
    }

    public function getAgeAttribute(): int
    {
        return now()->diffInYears($this->date_naissance);
    }

    public function getAdresseCompleteAttribute(): string
    {
        $adresse = $this->adresse;

        return $adresse['rue'] ?? ''.', '.($adresse['code_postal'] ?? '').' '.($adresse['ville'] ?? '').', '.($adresse['pays'] ?? '');
    }

    public function getEstActifAttribute(): bool
    {
        return $this->contrats()->where('statut_contrat', 'actif')->exists();
    }

    public function getTotalCapitalAssureAttribute(): float
    {
        return $this->contrats()->where('statut_contrat', 'actif')->sum('capital_assure');
    }

    public function getTotalPrimeAnnuelleAttribute(): float
    {
        return $this->contrats()->where('statut_contrat', 'actif')->sum('prime_annuelle');
    }

    // Scopes
    public function scopeVerifiesKyc($query)
    {
        return $query->where('kyc_verifie', true);
    }

    public function scopeAvecContratsActifs($query)
    {
        return $query->whereHas('contrats', function ($q) {
            $q->where('statut_contrat', 'actif');
        });
    }

    public function scopeParAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeAvecCotisationsEnRetard($query)
    {
        return $query->whereHas('contrats.cotisations', function ($q) {
            $q->where('statut_paiement', 'en_retard');
        });
    }

    // Méthodes métier
    public function verifierKyc(): void
    {
        $this->update([
            'kyc_verifie' => true,
            'date_verification_kyc' => now(),
        ]);
    }

    public function calculerScoreRisque(): float
    {
        $score = 50; // Score de base

        // Facteurs d'ajustement
        if ($this->age >= 60) {
            $score += 10;
        }
        if ($this->profession === 'Cadre') {
            $score -= 5;
        }
        if ($this->nombre_enfants > 2) {
            $score += 5;
        }
        if ($this->revenu_annuel < 30000) {
            $score += 10;
        }

        return min(max($score, 0), 100);
    }

    /**
     * Le "booted" méthode du modèle.
     */
    protected static function booted()
    {
        static::creating(function ($client) {
            // Générer la référence client si elle n'est pas déjà fournie
            if (empty($client->reference_client)) {
                $client->reference_client = static::generateReference();
            }
        });
    }

    /**
     * Génère une référence client unique.
     * Format : CLI + année (4 chiffres) + mois (2 chiffres) + numéro séquentiel sur 4 chiffres.
     * Exemple : CLI2025030012 pour mars 2025, 12ème client du mois.
     */
    public static function generateReference(): string
    {
        $prefix = 'CLI';
        $year = date('Y');
        $month = date('m');

        // Compter les clients créés ce mois-ci
        $count = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();

        $number = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return $prefix.$year.$month.$number;
    }
}
