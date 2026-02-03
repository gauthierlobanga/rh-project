<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AchatRachat extends Model
{
    protected $table = 'achats_rachats';

    protected $fillable = [
        'contrat_id',
        'type_operation',
        'date_operation',
        'montant_operation',
        'frais_operation',
        'montant_nette',
        'valeur_unitaire',
        'nombre_parts',
        'details_operation',
        'statut_operation',
        'valide_par',
        'date_validation',
        'motif_refus',
        'date_execution',
        'reference_operation',
        'parametres_fiscaux',
        'notes'
    ];

    protected $casts = [
        'date_operation' => 'date',
        'date_validation' => 'date',
        'date_execution' => 'date',
        'details_operation' => 'array',
        'parametres_fiscaux' => 'array',
        'montant_operation' => 'decimal:2',
        'frais_operation' => 'decimal:2',
        'montant_nette' => 'decimal:2',
        'valeur_unitaire' => 'decimal:4',
        'nombre_parts' => 'decimal:4'
    ];

    // Relations
    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContratAssuranceVie::class, 'contrat_id');
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    // Accesseurs
    public function getEstValideeAttribute(): bool
    {
        return $this->statut_operation === 'validee';
    }

    public function getEstExecuteeAttribute(): bool
    {
        return $this->statut_operation === 'executée';
    }

    public function getEstRefuseeAttribute(): bool
    {
        return $this->statut_operation === 'refusee';
    }

    // Scopes
    public function scopeValidees($query)
    {
        return $query->where('statut_operation', 'validee');
    }

    public function scopeParContrat($query, $contratId)
    {
        return $query->where('contrat_id', $contratId);
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type_operation', $type);
    }

    // Méthodes métier
    public function valider(User $validateur): bool
    {
        $this->update([
            'statut_operation' => 'validee',
            'date_validation' => now(),
            'valide_par' => $validateur->id
        ]);

        return true;
    }

    public function executer(): bool
    {
        $this->update([
            'statut_operation' => 'executée',
            'date_execution' => now()
        ]);

        // Créer un paiement pour le rachat
        if ($this->type_operation === 'rachat_partiel' || $this->type_operation === 'rachat_total') {
            Paiement::create([
                'contrat_id' => $this->contrat_id,
                'type_paiement' => 'rachat',
                'montant_paiement' => $this->montant_nette,
                'date_paiement' => now(),
                'mode_paiement' => 'virement',
                'statut_paiement' => 'valide',
                'valide_par' => $this->valide_par,
            ]);
        }

        return true;
    }

    public function refuser(string $motif): bool
    {
        $this->update([
            'statut_operation' => 'refusee',
            'motif_refus' => $motif
        ]);

        return true;
    }

    public function calculerMontantNet(): float
    {
        return $this->montant_operation - $this->frais_operation;
    }

    public function getImpactFiscal(): array
    {
        // Calcul des implications fiscales
        $ageContrat = $this->contrat->age_contrat;
        $montant = $this->montant_nette;

        if ($ageContrat >= 8) {
            // Exonération après 8 ans
            $impot = 0;
        } else {
            // Prélèvement forfaitaire unique
            $impot = $montant * 0.075; // 7.5%
        }

        $prelevementsSociaux = $montant * 0.172; // 17.2%

        return [
            'montant_brut' => $montant,
            'impot_sur_revenu' => $impot,
            'prelevements_sociaux' => $prelevementsSociaux,
            'montant_net' => $montant - $impot - $prelevementsSociaux,
        ];
    }
}
