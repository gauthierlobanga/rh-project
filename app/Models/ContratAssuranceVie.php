<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ContratAssuranceVie extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $table = 'contrat_assurance_vies';

    protected $fillable = [
        'numero_contrat',
        'souscripteur_id',
        'produit_id',
        'agent_id',
        'capital_assure',
        'prime_annuelle',
        'frequence_paiement',
        'montant_periodicite',
        'date_effet',
        'date_echeance',
        'duree_contrat',
        'statut_contrat',
        'mode_paiement',
        'coordonnees_paiement',
        'options_souscrites',
        'conditions_particulieres',
        'frais_gestion',
        'frais_entree',
        'frais_sortie',
        'participation_benefices',
        'date_signature',
        'date_validation',
        'date_resiliation',
        'motif_resiliation',
        'valeur_rachat',
        'valeur_epargne',
        'parametres_calcul',
        'numero_police',
    ];

    protected $casts = [
        'date_effet' => 'date',
        'date_echeance' => 'date',
        'date_signature' => 'date',
        'date_validation' => 'date',
        'date_resiliation' => 'date',
        'coordonnees_paiement' => 'array',
        'options_souscrites' => 'array',
        'parametres_calcul' => 'array',
        'capital_assure' => 'decimal:2',
        'prime_annuelle' => 'decimal:2',
        'montant_periodicite' => 'decimal:2',
        'valeur_rachat' => 'decimal:2',
        'valeur_epargne' => 'decimal:2',
        'frais_gestion' => 'decimal:2',
        'frais_entree' => 'decimal:2',
        'frais_sortie' => 'decimal:2',
        'participation_benefices' => 'decimal:2',
    ];

    // Relations
    public function souscripteur(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'souscripteur_id');
    }

    public function produit(): BelongsTo
    {
        return $this->belongsTo(ProduitAssurance::class, 'produit_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }

    /**
     * Un contrat peut avoir plusieurs bénéficiaires, cotisations, réserves, sinistres, commissions, paiements et historiques associés.
     * Chaque relation est définie avec une clé étrangère 'contrat_id' dans les tables correspondantes.
     */
    public function beneficiaires(): HasMany
    {
        return $this->hasMany(Beneficiaire::class, 'contrat_id');
    }

    public function cotisations(): HasMany
    {
        return $this->hasMany(Cotisation::class, 'contrat_id');
    }

    public function reserves(): HasMany
    {
        return $this->hasMany(ReserveActuarielle::class, 'contrat_id');
    }

    public function sinistres(): HasMany
    {
        return $this->hasMany(Sinistre::class, 'contrat_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'contrat_id');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class, 'contrat_id');
    }

    public function historique(): HasMany
    {
        return $this->hasMany(HistoriqueContrat::class, 'contrat_id');
    }

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('contrats_signes')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png'])
            ->singleFile();

        $this->addMediaCollection('avenants')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);

        $this->addMediaCollection('quittances')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);

        $this->addMediaCollection('documents_contrat')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'application/msword']);
    }

    /**
     * Assecceurs
     */
    public function getEstActifAttribute(): bool
    {
        return $this->statut_contrat === 'actif';
    }

    public function getEstEnRetardAttribute(): bool
    {
        return $this->cotisations()->where('statut_paiement', 'en_retard')->exists();
    }

    public function getMontantDuAttribute(): float
    {
        return $this->cotisations()
            ->where('statut_paiement', '!=', 'paye')
            ->where('date_echeance', '<=', now())
            ->sum('montant_due');
    }

    public function getProchaineEcheanceAttribute(): ?Carbon
    {
        $prochaine = $this->cotisations()
            ->where('statut_paiement', 'en_attente')
            ->where('date_echeance', '>', now())
            ->orderBy('date_echeance')
            ->first();

        return $prochaine ? $prochaine->date_echeance : null;
    }

    public function getJoursRestantsEcheanceAttribute(): ?int
    {
        if ($prochaine = $this->prochaine_echeance) {
            return now()->diffInDays($prochaine, false);
        }

        return null;
    }

    public function getReserveActuelleAttribute(): ?float
    {
        $reserve = $this->reserves()->latest('date_calcul')->first();

        return $reserve ? $reserve->reserve_totale : null;
    }

    public function getAgeContratAttribute(): int
    {
        return now()->diffInYears($this->date_effet);
    }

    public function getAnneeRestanteAttribute(): int
    {
        return now()->diffInYears($this->date_echeance);
    }

    // Scopes
    public function scopeActifs($query)
    {
        return $query->where('statut_contrat', 'actif');
    }

    public function scopeEnRetard($query)
    {
        return $query->whereHas('cotisations', function ($q) {
            $q->where('statut_paiement', 'en_retard');
        });
    }

    public function scopeParAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeParProduit($query, $produitId)
    {
        return $query->where('produit_id', $produitId);
    }

    public function scopeParClient($query, $clientId)
    {
        return $query->where('souscripteur_id', $clientId);
    }

    public function scopeAvecEcheanceProche($query, $jours = 30)
    {
        return $query->whereHas('cotisations', function ($q) use ($jours) {
            $q->where('statut_paiement', 'en_attente')
                ->whereBetween('date_echeance', [now(), now()->addDays($jours)]);
        });
    }

    // Méthodes métier
    public function calculerMontantPeriodicite(): float
    {
        $montants = [
            'mensuelle' => $this->prime_annuelle / 12,
            'trimestrielle' => $this->prime_annuelle / 4,
            'semestrielle' => $this->prime_annuelle / 2,
            'annuelle' => $this->prime_annuelle,
        ];

        return $montants[$this->frequence_paiement] ?? $this->prime_annuelle;
    }

    public function genererEcheancier(int $annees = 5): array
    {
        $echeances = [];
        $montant = $this->montant_periodicite;
        $date = Carbon::parse($this->date_effet);

        $increments = [
            'mensuelle' => 1,
            'trimestrielle' => 3,
            'semestrielle' => 6,
            'annuelle' => 12,
        ];

        $mois = $increments[$this->frequence_paiement] ?? 12;
        $totalEcheances = ($annees * 12) / $mois;

        for ($i = 1; $i <= $totalEcheances; $i++) {
            $echeanceDate = $date->copy()->addMonths(($i - 1) * $mois);

            $echeances[] = [
                'date_echeance' => $echeanceDate,
                'montant_due' => $montant,
                'numero_ordre' => $i,
            ];
        }

        return $echeances;
    }

    public function resiliation(string $motif): bool
    {
        $this->update([
            'statut_contrat' => 'resilie',
            'motif_resiliation' => $motif,
            'date_resiliation' => now(),
        ]);

        // Marquer les cotisations futures comme annulées
        $this->cotisations()
            ->where('date_echeance', '>', now())
            ->where('statut_paiement', 'en_attente')
            ->update(['statut_paiement' => 'annule']);

        return true;
    }

    public function calculerValeurRachat(): float
    {
        $reserve = $this->reserve_actuelle ?? 0;

        // Application des pénalités selon l'âge du contrat
        if ($this->age_contrat < 5) {
            $penalite = max(0, 0.08 - ($this->age_contrat * 0.016));
            $reserve *= (1 - $penalite);
        }

        // Déduction des frais de sortie
        if ($this->frais_sortie) {
            $reserve *= (1 - ($this->frais_sortie / 100));
        }

        return max(0, $reserve);
    }

    public function ajouterBeneficiaire(array $donnees): Beneficiaire
    {
        // Vérifier que le total des pourcentages ne dépasse pas 100%
        $totalActuel = $this->beneficiaires()->sum('pourcentage_attribution');
        $nouveauTotal = $totalActuel + $donnees['pourcentage_attribution'];

        if ($nouveauTotal > 100) {
            throw new \Exception('Le total des pourcentages attribués ne peut pas dépasser 100%');
        }

        return $this->beneficiaires()->create($donnees);
    }
}
