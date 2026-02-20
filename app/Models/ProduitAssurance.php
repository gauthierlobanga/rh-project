<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProduitAssurance extends Model
{
    protected $table = 'produit_assurances';

    protected $fillable = [
        'code_produit',
        'nom_produit',
        'description_courte',
        'description_longue',
        'categorie',
        'garanties_incluses',
        'exclusions',
        'options_disponibles',
        'age_entree_minimum',
        'age_entree_maximum',
        'age_maturite_maximum',
        'prime_minimale',
        'prime_maximale',
        'capital_minimum',
        'capital_maximum',
        'structure_commission',
        'parametres_actuariels',
        'conditions_particulieres',
        'est_actif',
        'date_activation',
        'date_desactivation',
        'document_contrat_type',
    ];

    protected $casts = [
        'garanties_incluses' => 'array',
        'exclusions' => 'array',
        'options_disponibles' => 'array',
        'structure_commission' => 'array',
        'parametres_actuariels' => 'array',
        'conditions_particulieres' => 'array',
        'prime_minimale' => 'decimal:2',
        'prime_maximale' => 'decimal:2',
        'capital_minimum' => 'decimal:2',
        'capital_maximum' => 'decimal:2',
        'est_actif' => 'boolean',
        'date_activation' => 'date',
        'date_desactivation' => 'date',
    ];

    // Relations
    public function contrats(): HasMany
    {
        return $this->hasMany(ContratAssuranceVie::class, 'produit_id');
    }

    // Accesseurs
    public function getNombreContratsAttribute(): int
    {
        return $this->contrats()->count();
    }

    public function getPrimeMoyenneAttribute(): ?float
    {
        $count = $this->contrats()->count();
        if ($count > 0) {
            return $this->contrats()->avg('prime_annuelle');
        }

        return null;
    }

    public function getCapitalMoyenAttribute(): ?float
    {
        $count = $this->contrats()->count();
        if ($count > 0) {
            return $this->contrats()->avg('capital_assure');
        }

        return null;
    }

    public function getTauxCommissionAcquisitionAttribute(): float
    {
        $structure = $this->structure_commission ?? [];

        return $structure['acquisition'] ?? 0;
    }

    public function getTauxCommissionRenouvellementAttribute(): float
    {
        $structure = $this->structure_commission ?? [];

        return $structure['renouvellement'] ?? 0;
    }

    // Scopes
    public function scopeActifs(Builder $query): Builder
    {
        return $query->where('est_actif', true);
    }

    public function scopeParCategorie(Builder $query, string $categorie): Builder
    {
        return $query->where('categorie', $categorie);
    }

    public function scopePourAge(Builder $query, int $age): Builder
    {
        return $query->where('age_entree_minimum', '<=', $age)
            ->where('age_entree_maximum', '>=', $age);
    }

    public function scopePourCapital(Builder $query, float $capital): Builder
    {
        return $query->where('capital_minimum', '<=', $capital)
            ->where(function ($q) use ($capital) {
                $q->whereNull('capital_maximum')
                    ->orWhere('capital_maximum', '>=', $capital);
            });
    }

    // Méthodes métier
    public function calculerPrimeTheorique(float $capital, int $age, int $duree): ?float
    {
        if ($age < $this->age_entree_minimum || $age > $this->age_entree_maximum) {
            return null;
        }

        if ($capital < $this->capital_minimum || ($this->capital_maximum && $capital > $this->capital_maximum)) {
            return null;
        }

        // Calcul simplifié - en réalité, utiliser les paramètres actuariels
        $parametres = $this->parametres_actuariels ?? [];
        $taux_base = $parametres['taux_base'] ?? 0.01;
        $majoration_age = max(0, $age - 30) * 0.002;

        $prime = $capital * ($taux_base + $majoration_age);

        // Ajustement pour durée
        if ($duree > 10) {
            $prime *= 0.9; // Réduction pour longue durée
        }

        return max($prime, $this->prime_minimale);
    }

    public function estEligiblePourClient(Client $client): array
    {
        $age = $client->age;
        $capital = $client->revenu_annuel * 5; // Capital recommandé

        $eligibilite = [
            'eligible' => true,
            'raisons' => [],
        ];

        if ($age < $this->age_entree_minimum) {
            $eligibilite['eligible'] = false;
            $eligibilite['raisons'][] = "Âge minimum requis : {$this->age_entree_minimum} ans";
        }

        if ($age > $this->age_entree_maximum) {
            $eligibilite['eligible'] = false;
            $eligibilite['raisons'][] = "Âge maximum autorisé : {$this->age_entree_maximum} ans";
        }

        if ($capital < $this->capital_minimum) {
            $eligibilite['eligible'] = false;
            $eligibilite['raisons'][] = 'Capital minimum requis : '.number_format($this->capital_minimum, 0, ',', ' ').' €';
        }

        if ($this->capital_maximum && $capital > $this->capital_maximum) {
            $eligibilite['eligible'] = false;
            $eligibilite['raisons'][] = 'Capital maximum autorisé : '.number_format($this->capital_maximum, 0, ',', ' ').' €';
        }

        return $eligibilite;
    }
}
