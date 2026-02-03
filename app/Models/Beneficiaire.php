<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Beneficiaire extends Model
{
    protected $table = 'beneficiaires';

    protected $fillable = [
        'user_id',
        'contrat_id',
        'nom',
        'prenom',
        'date_naissance',
        'lien_parente',
        'pourcentage_attribution',
        'est_beneficiaire_primaire',
        'coordonnees_contact',
        'numero_cni',
        'date_effet_attribution',
        'date_fin_attribution',
        'statut_beneficiaire',
        'conditions_particulieres',
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'date_effet_attribution' => 'date',
        'date_fin_attribution' => 'date',
        'coordonnees_contact' => 'array',
        'pourcentage_attribution' => 'decimal:2',
        'est_beneficiaire_primaire' => 'boolean',
    ];

    // Relations
    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContratAssuranceVie::class, 'contrat_id');
    }

    // Accesseurs
    public function getNomCompletAttribute(): string
    {
        return $this->prenom.' '.$this->nom;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_naissance ? now()->diffInYears($this->date_naissance) : null;
    }

    public function getEstActifAttribute(): bool
    {
        return $this->statut_beneficiaire === 'actif';
    }

    public function getMontantAttribueAttribute(): ?float
    {
        if ($this->contrat->capital_assure && $this->pourcentage_attribution) {
            return $this->contrat->capital_assure * ($this->pourcentage_attribution / 100);
        }

        return null;
    }

    // Scopes
    public function scopePrimaires($query)
    {
        return $query->where('est_beneficiaire_primaire', true);
    }

    public function scopeActifs($query)
    {
        return $query->where('statut_beneficiaire', 'actif');
    }

    public function scopeParContrat($query, $contratId)
    {
        return $query->where('contrat_id', $contratId);
    }

    // Méthodes métier
    public function estEligiblePourSinistre(string $typeSinistre): bool
    {
        if ($this->statut_beneficiaire !== 'actif') {
            return false;
        }

        // Vérifier les dates d'attribution
        if ($this->date_effet_attribution && $this->date_effet_attribution > now()) {
            return false;
        }

        if ($this->date_fin_attribution && $this->date_fin_attribution < now()) {
            return false;
        }

        return true;
    }

    public function mettreAJourPourcentage(float $nouveauPourcentage): bool
    {
        // Vérifier que le nouveau total ne dépasse pas 100%
        $totalAutres = $this->contrat->beneficiaires()
            ->where('id', '!=', $this->id)
            ->sum('pourcentage_attribution');

        if (($totalAutres + $nouveauPourcentage) > 100) {
            return false;
        }

        $this->update(['pourcentage_attribution' => $nouveauPourcentage]);

        return true;
    }
}
