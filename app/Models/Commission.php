<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    protected $table = 'commissions';

    protected $fillable = [
        'agent_id',
        'contrat_id',
        'cotisation_id',
        'type_commission',
        'montant_prime',
        'taux_commission',
        'montant_commission',
        'date_calcul',
        'date_paiement',
        'statut_commission',
        'numero_paiement',
        'details_calcul',
        'annee_comptable',
        'mois_comptable',
        'taux_tva',
        'montant_tva',
        'montant_net',
        'notes',
    ];

    protected $casts = [
        'date_calcul' => 'date',
        'date_paiement' => 'date',
        'details_calcul' => 'array',
        'montant_prime' => 'decimal:2',
        'taux_commission' => 'decimal:2',
        'montant_commission' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_net' => 'decimal:2',
    ];

    // Relations
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContratAssuranceVie::class, 'contrat_id');
    }

    public function cotisation(): BelongsTo
    {
        return $this->belongsTo(Cotisation::class, 'cotisation_id');
    }

    // Accesseurs
    public function getEstPayeeAttribute(): bool
    {
        return $this->statut_commission === 'payee';
    }

    public function getEstAPayerAttribute(): bool
    {
        return $this->statut_commission === 'a_payer';
    }

    public function getEstCalculeeAttribute(): bool
    {
        return $this->statut_commission === 'calculee';
    }

    // Scopes
    public function scopePayees($query)
    {
        return $query->where('statut_commission', 'payee');
    }

    public function scopeAPayer($query)
    {
        return $query->where('statut_commission', 'a_payer');
    }

    public function scopeParAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeParPeriode($query, $annee, $mois = null)
    {
        $query = $query->where('annee_comptable', $annee);

        if ($mois) {
            $query->where('mois_comptable', $mois);
        }

        return $query;
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type_commission', $type);
    }

    // Méthodes métier
    public function calculerMontantNet(): float
    {
        $tva = $this->taux_tva ?? 0;
        $montantTva = $this->montant_commission * ($tva / 100);

        return $this->montant_commission - $montantTva;
    }

    public function marquerCommePayee(string $numeroPaiement): bool
    {
        $this->update([
            'statut_commission' => 'payee',
            'date_paiement' => now(),
            'numero_paiement' => $numeroPaiement,
            'montant_net' => $this->calculerMontantNet(),
        ]);

        return true;
    }

    public function genererFichePaie(): array
    {
        return [
            'agent' => $this->agent->utilisateur->name,
            'matricule' => $this->agent->matricule_agent,
            'date_paiement' => $this->date_paiement ?? now(),
            'type_commission' => $this->type_commission,
            'contrat' => $this->contrat->numero_contrat,
            'prime' => $this->montant_prime,
            'taux_commission' => $this->taux_commission,
            'commission_brute' => $this->montant_commission,
            'taux_tva' => $this->taux_tva,
            'montant_tva' => $this->montant_tva,
            'commission_nette' => $this->montant_net,
            'numero_paiement' => $this->numero_paiement,
        ];
    }
}
