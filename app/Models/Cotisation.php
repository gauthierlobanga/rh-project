<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Cotisation extends Model
{
    protected $table = 'cotisations';

    protected $fillable = [
        'contrat_id',
        'date_echeance',
        'date_paiement',
        'montant_due',
        'montant_paye',
        'statut_paiement',
        'numero_facture',
        'details_paiement',
        'penalite_retard',
        'interets_moratoires',
        'mode_paiement',
        'reference_paiement',
        'date_encaissement',
        'notes',
        'est_rappele',
        'date_rappel',
        'nombre_relances',
    ];

    protected $casts = [
        'date_echeance' => 'date',
        'date_paiement' => 'date',
        'date_encaissement' => 'date',
        'date_rappel' => 'date',
        'details_paiement' => 'array',
        'montant_due' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'penalite_retard' => 'decimal:2',
        'interets_moratoires' => 'decimal:2',
        'est_rappele' => 'boolean',
    ];

    // Relations
    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContratAssuranceVie::class, 'contrat_id');
    }

    public function paiement(): HasOne
    {
        return $this->hasOne(Paiement::class, 'cotisation_id');
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class, 'cotisation_id');
    }

    // Accesseurs
    public function getEstPayeeAttribute(): bool
    {
        return $this->statut_paiement === 'paye';
    }

    public function getEstEnRetardAttribute(): bool
    {
        return $this->statut_paiement === 'en_retard' ||
            ($this->statut_paiement === 'en_attente' && $this->date_echeance < now());
    }

    public function getMontantRestantAttribute(): float
    {
        return max(0, $this->montant_due - $this->montant_paye);
    }

    public function getJoursRetardAttribute(): ?int
    {
        if ($this->est_en_retard) {
            return now()->diffInDays($this->date_echeance);
        }

        return null;
    }

    public function getEstPartiellementPayeeAttribute(): bool
    {
        return $this->montant_paye > 0 && $this->montant_paye < $this->montant_due;
    }

    public function getMontantTotalDuAttribute(): float
    {
        return $this->montant_restant + $this->penalite_retard + $this->interets_moratoires;
    }

    // Scopes
    public function scopeEnAttente($query)
    {
        return $query->where('statut_paiement', 'en_attente');
    }

    public function scopeEnRetard($query)
    {
        return $query->where(function ($q) {
            $q->where('statut_paiement', 'en_retard')
                ->orWhere(function ($q2) {
                    $q2->where('statut_paiement', 'en_attente')
                        ->where('date_echeance', '<', now());
                });
        });
    }

    public function scopePayees($query)
    {
        return $query->where('statut_paiement', 'paye');
    }

    public function scopeParContrat($query, $contratId)
    {
        return $query->where('contrat_id', $contratId);
    }

    public function scopeParPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_echeance', [$debut, $fin]);
    }

    public function scopeAvecRappel($query)
    {
        return $query->where('est_rappele', true);
    }

    // Méthodes métier
    public function marquerCommePayee(array $details): bool
    {
        $this->update([
            'statut_paiement' => 'paye',
            'date_paiement' => now(),
            'montant_paye' => $this->montant_due,
            'details_paiement' => $details,
            'mode_paiement' => $details['mode_paiement'] ?? null,
            'reference_paiement' => $details['reference'] ?? null,
            'date_encaissement' => now(),
        ]);

        // Enregistrer le paiement
        Paiement::create([
            'cotisation_id' => $this->id,
            'type_paiement' => 'cotisation',
            'montant_paiement' => $this->montant_due,
            'date_paiement' => now(),
            'mode_paiement' => $details['mode_paiement'] ?? 'virement',
            'reference_paiement' => $details['reference'] ?? null,
            'statut_paiement' => 'valide',
            'valide_par' => Auth::user()->id,
        ]);

        return true;
    }

    public function appliquerPenaliteRetard(): void
    {
        if (! $this->est_en_retard || $this->jours_retard <= 30) {
            return;
        }

        $penalite = $this->montant_due * 0.05; // 5% de pénalité
        $this->update([
            'penalite_retard' => $penalite,
            'statut_paiement' => 'en_retard',
        ]);
    }

    public function calculerInteretsMoratoires(): float
    {
        if (! $this->est_en_retard) {
            return 0;
        }

        $jours = $this->jours_retard;
        $tauxJournalier = 0.0003; // Taux d'intérêt légal journalisé
        $capital = $this->montant_restant;

        return $capital * $tauxJournalier * $jours;
    }

    public function envoyerRappel(): bool
    {
        if ($this->est_rappele || $this->est_payee) {
            return false;
        }

        // Logique d'envoi de rappel
        $this->update([
            'est_rappele' => true,
            'date_rappel' => now(),
            'nombre_relances' => $this->nombre_relances + 1,
        ]);

        return true;
    }

    public function genererFacture(): array
    {
        return [
            'numero_facture' => $this->numero_facture ?? 'FAC-'.$this->id.'-'.date('Ymd'),
            'date_facture' => now(),
            'client' => $this->contrat->souscripteur->utilisateur->name,
            'contrat' => $this->contrat->numero_contrat,
            'montant_ht' => $this->montant_due,
            'tva' => 0, // Exonéré d'assurance vie
            'montant_ttc' => $this->montant_due,
            'penalites' => $this->penalite_retard + $this->interets_moratoires,
            'total_a_payer' => $this->montant_total_du,
            'date_echeance' => $this->date_echeance,
            'iban' => $this->contrat->coordonnees_paiement['iban'] ?? null,
        ];
    }
}
