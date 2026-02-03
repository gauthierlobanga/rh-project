<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    protected $table = 'paiements';

    protected $fillable = [
        'cotisation_id',
        'sinistre_id',
        'contrat_id',
        'type_paiement',
        'montant_paiement',
        'date_paiement',
        'mode_paiement',
        'reference_paiement',
        'numero_cheque',
        'numero_virement',
        'titulaire_compte',
        'coordonnees_bancaires',
        'statut_paiement',
        'date_validation',
        'valide_par',
        'motif_refus',
        'est_recurrent',
        'frequence_recurrence',
        'prochaine_echeance',
        'details_paiement',
        'notes',
    ];

    protected $casts = [
        'date_paiement' => 'date',
        'date_validation' => 'date',
        'prochaine_echeance' => 'date',
        'coordonnees_bancaires' => 'array',
        'details_paiement' => 'array',
        'montant_paiement' => 'decimal:2',
        'est_recurrent' => 'boolean',
    ];

    // Relations
    public function cotisation(): BelongsTo
    {
        return $this->belongsTo(Cotisation::class, 'cotisation_id');
    }

    public function sinistre(): BelongsTo
    {
        return $this->belongsTo(Sinistre::class, 'sinistre_id');
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContratAssuranceVie::class, 'contrat_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    // Accesseurs
    public function getEstValideAttribute(): bool
    {
        return $this->statut_paiement === 'valide';
    }

    public function getEstRefuseAttribute(): bool
    {
        return $this->statut_paiement === 'refuse';
    }

    public function getEstEnCoursAttribute(): bool
    {
        return $this->statut_paiement === 'en_cours';
    }

    // Scopes
    public function scopeValides($query)
    {
        return $query->where('statut_paiement', 'valide');
    }

    public function scopeParContrat($query, $contratId)
    {
        return $query->where('contrat_id', $contratId);
    }

    public function scopeParPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_paiement', [$debut, $fin]);
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type_paiement', $type);
    }

    // Méthodes métier
    public function valider(User $validateur): bool
    {
        $this->update([
            'statut_paiement' => 'valide',
            'date_validation' => now(),
            'valide_par' => $validateur->id,
        ]);

        return true;
    }

    public function refuser(string $motif): bool
    {
        $this->update([
            'statut_paiement' => 'refuse',
            'motif_refus' => $motif,
        ]);

        return true;
    }

    public function genererReleve(): array
    {
        return [
            'numero_paiement' => $this->id,
            'date_paiement' => $this->date_paiement,
            'montant' => $this->montant_paiement,
            'mode_paiement' => $this->mode_paiement,
            'reference' => $this->reference_paiement,
            'statut' => $this->statut_paiement,
            'beneficiaire' => $this->getBeneficiaireInfo(),
            'details' => $this->details_paiement,
        ];
    }

    private function getBeneficiaireInfo(): ?string
    {
        if ($this->cotisation_id) {
            return $this->cotisation->contrat->souscripteur->utilisateur?->name;
        }

        if ($this->sinistre_id) {
            return $this->sinistre->contrat->souscripteur->utilisateur?->name;
        }

        return null;
    }
}
