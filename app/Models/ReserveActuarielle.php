<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReserveActuarielle extends Model
{
    protected $table = 'reserves_actuarielles';

    protected $fillable = [
        'contrat_id',
        'date_calcul',
        'periode_calcul',
        'methode_calcul',
        'reserve_technique',
        'provision_risque',
        'provision_previsionnelle',
        'reserve_totale',
        'taux_actualisation',
        'taux_technique',
        'flux_futurs_projetes',
        'scenario_calcul',
        'parametres_actuariels',
        //
        'resultats_detailes',
        'statut_calcul',
        'valide_par',
        'date_validation',
        'notes',
        'ecart_avec_precedent',
        'evolution_percentage',
        'reserve_minimale_legale',
        'marge_solvabilite',
    ];

    protected $casts = [
        'date_calcul' => 'date',
        'date_validation' => 'date',
        'parametres_actuariels' => 'array',
        'resultats_detailes' => 'array',
        'flux_futurs_projetes' => 'array',
        'reserve_technique' => 'decimal:2',
        'provision_risque' => 'decimal:2',
        'provision_previsionnelle' => 'decimal:2',
        'reserve_totale' => 'decimal:2',
        'taux_actualisation' => 'decimal:4',
        'taux_technique' => 'decimal:4',
        'ecart_avec_precedent' => 'decimal:2',
        'evolution_percentage' => 'decimal:2',
        'reserve_minimale_legale' => 'decimal:2',
        'marge_solvabilite' => 'decimal:2',
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

    public function precedent(): HasOne
    {
        return $this->hasOne(ReserveActuarielle::class, 'contrat_id', 'contrat_id')
            ->where('id', '<', $this->id)
            ->orderBy('id', 'desc');
    }

    // Accesseurs
    public function getEstValideeAttribute(): bool
    {
        return $this->statut_calcul === 'validee';
    }

    public function getEstCalculeeAttribute(): bool
    {
        return $this->statut_calcul === 'calculee';
    }

    public function getEstEnCoursAttribute(): bool
    {
        return $this->statut_calcul === 'en_cours';
    }

    public function getReserveParUniteAttribute(): ?float
    {
        if ($this->contrat && $this->contrat->nombre_parts > 0) {
            return $this->reserve_totale / $this->contrat->nombre_parts;
        }

        return null;
    }

    public function getCouververtureReserveAttribute(): ?float
    {
        if ($this->contrat && $this->contrat->valeur_rachat > 0) {
            return ($this->reserve_totale / $this->contrat->valeur_rachat) * 100;
        }

        return null;
    }

    // Scopes
    public function scopeValidees($query)
    {
        return $query->where('statut_calcul', 'validee');
    }

    public function scopeParContrat($query, $contratId)
    {
        return $query->where('contrat_id', $contratId);
    }

    public function scopeParPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('date_calcul', [$debut, $fin]);
    }

    public function scopeDerniereParContrat($query)
    {
        return $query->selectRaw('DISTINCT ON (contrat_id) *')
            ->orderBy('contrat_id')
            ->orderBy('date_calcul', 'desc');
    }

    // Méthodes métier
    public function calculerReserve(): array
    {
        $contrat = $this->contrat;
        $age = $contrat->age_contrat;
        $capital = $contrat->capital_assure;
        $prime = $contrat->prime_annuelle;

        // Exemple de calcul simplifié (à adapter avec les formules actuarielles réelles)
        $taux_tech = $this->taux_technique ?? 0.02;
        $taux_actu = $this->taux_actualisation ?? 0.015;

        // Calcul de la réserve technique (approche simplifiée)
        $reserve_technique = $prime * ((1 - pow(1 + $taux_tech, -$age)) / $taux_tech);

        // Provision pour risque (pourcentage du capital)
        $provision_risque = $capital * 0.001 * $age;

        // Provision prévisionnelle pour participation aux bénéfices
        $provision_previsionnelle = $reserve_technique * 0.05;

        $reserve_totale = $reserve_technique + $provision_risque + $provision_previsionnelle;

        return [
            'reserve_technique' => $reserve_technique,
            'provision_risque' => $provision_risque,
            'provision_previsionnelle' => $provision_previsionnelle,
            'reserve_totale' => $reserve_totale,
        ];
    }

    public function valider(User $validateur): bool
    {
        $this->update([
            'statut_calcul' => 'validee',
            'date_validation' => now(),
            'valide_par' => $validateur->id,
        ]);

        return true;
    }

    public function calculerEvolution(): array
    {
        $precedent = $this->precedent;

        if (! $precedent) {
            return [
                'ecart' => 0,
                'evolution' => 0,
                'tendance' => 'stable',
            ];
        }

        $ecart = $this->reserve_totale - $precedent->reserve_totale;
        $evolution = $precedent->reserve_totale > 0
            ? ($ecart / $precedent->reserve_totale) * 100
            : 0;

        $tendance = match (true) {
            $evolution > 5 => 'forte_hausse',
            $evolution > 0 => 'hausse',
            $evolution < -5 => 'forte_baisse',
            $evolution < 0 => 'baisse',
            default => 'stable'
        };

        return [
            'ecart' => $ecart,
            'evolution' => $evolution,
            'tendance' => $tendance,
            'date_precedent' => $precedent->date_calcul,
        ];
    }

    public function verifierConformiteLegale(): array
    {
        $reserve_minimale = $this->reserve_minimale_legale ??
            ($this->contrat->capital_assure * 0.85); // 85% du capital

        $conforme = $this->reserve_totale >= $reserve_minimale;

        return [
            'conforme' => $conforme,
            'reserve_minimale' => $reserve_minimale,
            'ecart_minimum' => $this->reserve_totale - $reserve_minimale,
            'pourcentage_couverture' => ($this->reserve_totale / $reserve_minimale) * 100,
        ];
    }

    public function genererRapportActuariel(): array
    {
        $evolution = $this->calculerEvolution();
        $conformite = $this->verifierConformiteLegale();

        return [
            'contrat' => $this->contrat->numero_contrat,
            'date_calcul' => $this->date_calcul,
            'methode' => $this->methode_calcul,
            'reserves' => [
                'technique' => $this->reserve_technique,
                'risque' => $this->provision_risque,
                'previsionnelle' => $this->provision_previsionnelle,
                'totale' => $this->reserve_totale,
            ],
            'taux' => [
                'technique' => $this->taux_technique,
                'actualisation' => $this->taux_actualisation,
            ],
            'evolution' => $evolution,
            'conformite_legale' => $conformite,
            'statut' => $this->statut_calcul,
            'valide_par' => $this->validateur->name ?? null,
        ];
    }
}
