<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class HistoriqueContrat extends Model
{
    protected $table = 'historique_contrats';

    protected $fillable = [
        'contrat_id',
        'type_evenement',
        'description',
        'donnees_avant',
        'donnees_apres',
        'champs_modifies',
        'utilisateur_id',
        'utilisateur_type',
        'ip_adresse',
        'user_agent',
        'niveau_importance',
        'tags',
        'reference_liee',
        'type_reference_liee',
        'date_effet',
        'date_validation',
        'valide_par',
        'statut_evenement',
        'notes',
    ];

    protected $casts = [
        'donnees_avant' => 'array',
        'donnees_apres' => 'array',
        'champs_modifies' => 'array',
        'tags' => 'array',
        'date_effet' => 'datetime',
        'date_validation' => 'datetime',
        'niveau_importance' => 'integer',
    ];

    // Relations
    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContratAssuranceVie::class, 'contrat_id');
    }

    public function utilisateur(): MorphTo
    {
        return $this->morphTo();
    }

    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    // Accesseurs
    public function getEstValideAttribute(): bool
    {
        return $this->statut_evenement === 'valide';
    }

    public function getEstAnnuleAttribute(): bool
    {
        return $this->statut_evenement === 'annule';
    }

    public function getEstEnAttenteAttribute(): bool
    {
        return $this->statut_evenement === 'en_attente';
    }

    public function getChangementsResumesAttribute(): string
    {
        $champs = $this->champs_modifies ?? [];

        if (empty($champs)) {
            return 'Aucun champ modifié';
        }

        $resumes = [];
        foreach ($champs as $champ => $valeurs) {
            if (is_array($valeurs)) {
                $avant = $valeurs['avant'] ?? null;
                $apres = $valeurs['apres'] ?? null;
                $resumes[] = "$champ: $avant → $apres";
            }
        }

        return implode('; ', $resumes);
    }

    public function getImpactAttribute(): string
    {
        $type = $this->type_evenement;

        $impacts = [
            'modification_beneficiaire' => 'majeur',
            'changement_capital' => 'majeur',
            'modification_prime' => 'moyen',
            'changement_mode_paiement' => 'mineur',
            'mise_a_jour_coordonnees' => 'mineur',
            'resiliation' => 'critique',
            'rachat' => 'majeur',
            'arbitrage' => 'moyen',
        ];

        return $impacts[$type] ?? 'informationnel';
    }

    // Scopes
    public function scopeParContrat($query, $contratId)
    {
        return $query->where('contrat_id', $contratId);
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type_evenement', $type);
    }

    public function scopeParUtilisateur($query, $utilisateurId)
    {
        return $query->where('utilisateur_id', $utilisateurId);
    }

    public function scopeValides($query)
    {
        return $query->where('statut_evenement', 'valide');
    }

    public function scopeParPeriode($query, $debut, $fin)
    {
        return $query->whereBetween('created_at', [$debut, $fin]);
    }

    public function scopeParImportance($query, $niveau)
    {
        return $query->where('niveau_importance', '>=', $niveau);
    }

    // Méthodes métier
    public function enregistrerModification(array $avant, array $apres, array $champs): void
    {
        $this->update([
            'donnees_avant' => $avant,
            'donnees_apres' => $apres,
            'champs_modifies' => $champs,
        ]);
    }

    public function valider(User $validateur): bool
    {
        $this->update([
            'statut_evenement' => 'valide',
            'valide_par' => $validateur->id,
            'date_validation' => now(),
        ]);

        return true;
    }

    public function annuler(string $motif): bool
    {
        $this->update([
            'statut_evenement' => 'annule',
            'notes' => $this->notes."\n[Annulé le ".now()->format('Y-m-d H:i')."] Motif: $motif",
        ]);

        // Restaurer les données d'avant si nécessaire
        if ($this->type_evenement === 'modification') {
            $this->restaurerDonnees();
        }

        return true;
    }

    private function restaurerDonnees(): void
    {
        $contrat = $this->contrat;
        $donnees = $this->donnees_avant;

        if ($donnees && $contrat) {
            $champs = $this->champs_modifies;

            foreach ($champs as $champ => $valeurs) {
                if (isset($donnees[$champ]) && isset($valeurs['avant'])) {
                    $contrat->{$champ} = $valeurs['avant'];
                }
            }

            $contrat->save();
        }
    }

    public function genererRapportAudit(): array
    {
        return [
            'evenement' => [
                'type' => $this->type_evenement,
                'date' => $this->created_at,
                'description' => $this->description,
                'impact' => $this->impact,
                'importance' => $this->niveau_importance,
            ],
            'acteur' => [
                'utilisateur' => $this->utilisateur_type.':'.$this->utilisateur_id,
                'ip' => $this->ip_adresse,
                'user_agent' => $this->user_agent,
            ],
            'changements' => [
                'resume' => $this->changements_resumes,
                'champs_modifies' => $this->champs_modifies,
                'donnees_completes' => [
                    'avant' => $this->donnees_avant,
                    'apres' => $this->donnees_apres,
                ],
            ],
            'validation' => [
                'statut' => $this->statut_evenement,
                'valide_par' => $this->validateur->name ?? null,
                'date_validation' => $this->date_validation,
            ],
            'contrat' => [
                'numero' => $this->contrat->numero_contrat,
                'souscripteur' => $this->contrat->souscripteur->utilisateur->name,
            ],
        ];
    }

    public function calculerNiveauImportance(): int
    {
        $type = $this->type_evenement;

        $importances = [
            'resiliation' => 10,
            'rachat_total' => 9,
            'changement_capital' => 8,
            'modification_beneficiaire' => 7,
            'arbitrage' => 6,
            'modification_prime' => 5,
            'rachat_partiel' => 4,
            'changement_mode_paiement' => 3,
            'mise_a_jour_coordonnees' => 2,
            'consultation' => 1,
        ];

        return $importances[$type] ?? 0;
    }

    public static function creerEvenement(
        ContratAssuranceVie $contrat,
        string $type,
        array $donnees,
        $utilisateur = null,
        ?string $description = null
    ): self {
        $importance = (new static)->calculerNiveauImportanceParType($type);

        return self::create([
            'contrat_id' => $contrat->id,
            'type_evenement' => $type,
            'description' => $description ?? self::genererDescriptionParType($type),
            'utilisateur_id' => $utilisateur ? $utilisateur->id : Auth::user()->id,
            'utilisateur_type' => $utilisateur ? get_class($utilisateur) : 'User',
            'ip_adresse' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'niveau_importance' => $importance,
            'statut_evenement' => 'en_attente',
            'donnees_apres' => $donnees,
            'tags' => self::genererTagsParType($type),
        ]);
    }

    private function calculerNiveauImportanceParType(string $type): int
    {
        return $this->calculerNiveauImportance();
    }

    protected static function genererDescriptionParType(string $type): string
    {
        $descriptions = [
            'modification_beneficiaire' => 'Modification de la liste des bénéficiaires',
            'changement_capital' => 'Ajustement du capital assuré',
            'modification_prime' => 'Modification de la prime annuelle',
            'resiliation' => 'Résiliation du contrat',
            'rachat_total' => 'Rachat total du contrat',
            'rachat_partiel' => 'Rachat partiel',
        ];

        return $descriptions[$type] ?? 'Événement sur le contrat';
    }

    protected static function genererTagsParType(string $type): array
    {
        $tagsMapping = [
            'modification_beneficiaire' => ['beneficiaire', 'modification', 'contrat'],
            'changement_capital' => ['capital', 'modification', 'financier'],
            'resiliation' => ['resiliation', 'fin', 'contrat'],
            'rachat' => ['rachat', 'financier', 'liquidation'],
        ];

        return $tagsMapping[$type] ?? ['general'];
    }

    public function comparerAvec(HistoriqueContrat $autre): array
    {
        $champsCommuns = array_intersect(
            array_keys($this->champs_modifies ?? []),
            array_keys($autre->champs_modifies ?? [])
        );

        $differences = [];
        foreach ($champsCommuns as $champ) {
            $valeur1 = $this->champs_modifies[$champ]['apres'] ?? null;
            $valeur2 = $autre->champs_modifies[$champ]['apres'] ?? null;

            if ($valeur1 !== $valeur2) {
                $differences[$champ] = [
                    'historique1' => $valeur1,
                    'historique2' => $valeur2,
                    'identique' => false,
                ];
            }
        }

        return [
            'comparaison' => [
                'date1' => $this->created_at,
                'date2' => $autre->created_at,
                'evenement1' => $this->type_evenement,
                'evenement2' => $autre->type_evenement,
                'utilisateur1' => $this->utilisateur_id,
                'utilisateur2' => $autre->utilisateur_id,
            ],
            'differences' => $differences,
            'nombre_differences' => count($differences),
        ];
    }
}
