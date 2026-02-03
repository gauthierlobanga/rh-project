<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use App\Notifications\EmpruntApprouve;
use App\Notifications\ArgentDisponible;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\Factory;

class Emprunt extends Model
{
    use SoftDeletes;
    use Searchable;

    protected $fillable = [
        'user_id',
        'conseiller_id',
        'montant_emprunt',
        'date_fin_remboursement',
        // Taux définis par la banque
        'taux_interet_annuel',
        'taux_interet_mensuel',
        // Paramètres choisis par le client
        'type_amortissement',
        'frequence_paiement',
        'date_debut',
        'duree_mois',
        'duree_jours',
        // Paiement automatique
        'paiement_automatique',
        'iban',
        'bic',
        'prochaine_date_prelevement',
        'dernier_prelevement',
        // Frais
        'frais_dossier',
        'frais_assurance',
        'frais_notaire',
        'frais_autres',
        // Calculs
        'montant_mensualite',
        'total_interets',
        'total_a_rembourser',
        'total_frais',
        'montant_total_du',
        'taeg',
        // Dates
        'date_etude',
        'date_approbation',
        'date_signature',
        'date_deblocage',
        // Évaluation bancaire
        'score_credit',
        'capacite_remboursement',
        'endettement_actuel',
        'verification_identite',
        'verification_revenus',
        'verification_emploi',
        'est_actif',
        // Notifications
        'notifie_approuve',
        'notifie_fonds_disponibles',
        'date_notification_approuve',
        'date_notification_fonds',
        // Statut
        'status',
        'notes'
    ];

    protected function casts(): array
    {
        return  [
            'date_debut' => 'date',
            'date_fin_remboursement' => 'date',
            'date_etude' => 'date',
            'date_approbation' => 'date',
            'date_signature' => 'date',
            'date_deblocage' => 'date',
            'prochaine_date_prelevement' => 'date',
            'dernier_prelevement' => 'date',
            'paiement_automatique' => 'boolean',
            'verification_identite' => 'boolean',
            'verification_revenus' => 'boolean',
            'verification_emploi' => 'boolean',
            'est_actif' => 'boolean',
            'notifie_approuve' => 'boolean',
            'notifie_fonds_disponibles' => 'boolean',
            'date_notification_approuve' => 'datetime',
            'date_notification_fonds' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function echeances()
    {
        return $this->hasMany(Echeance::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function frais()
    {
        return $this->hasMany(FraisEmprunt::class);
    }

    public function notifierApprobation()
    {
        $this->user->notify(new EmpruntApprouve($this));
        $this->marquerNotifieApprouve();

        // Émettre un événement Livewire
        $this->dispatch('notification-received');
    }

    public function notifierFondsDisponibles()
    {
        $this->user->notify(new ArgentDisponible($this));
        $this->marquerNotifieFondsDisponibles();

        // Émettre un événement Livewire
        $this->dispatch('notification-received');
    }

    // Dans Filament, après l'approbation d'un emprunt :
    public function approuverEmprunt()
    {
        $this->emprunt->approuver();
        $this->emprunt->notifierApprobation();

        Notification::make()
            ->title('Emprunt approuvé')
            ->body('Notification envoyée au client')
            ->success()
            ->send();
    }
    /**
     * Méthode pour calculer le TAEG
     */
    public function calculerTAEG()
    {
        // Formule simplifiée du TAEG
        // En réalité, le TAEG nécessite une résolution numérique
        // Ici, on utilise une approximation

        $capital = $this->montant_emprunt;
        $mensualite = $this->montant_mensualite;
        $nombreMensualites = $this->duree_mois;
        $totalFrais = $this->frais_dossier + $this->frais_assurance + $this->frais_notaire + $this->frais_autres;

        // Capital effectif (montant reçu par l'emprunteur)
        $capitalEffectif = $capital - $totalFrais;

        if ($capitalEffectif <= 0) {
            return $this->taux_interet_annuel;
        }

        // Calcul approximatif du TAEG par itération
        $tauxMensuel = 0.01; // taux initial 1% mensuel
        $precision = 0.000001;
        $maxIterations = 1000;

        for ($i = 0; $i < $maxIterations; $i++) {
            $calcul = $mensualite * (1 - pow(1 + $tauxMensuel, -$nombreMensualites)) / $tauxMensuel;
            $erreur = $calcul - $capitalEffectif;

            if (abs($erreur) < $precision) {
                break;
            }

            // Ajustement du taux (méthode de Newton simplifiée)
            $tauxMensuel = $tauxMensuel - ($erreur / ($capitalEffectif * 100));
        }

        // Conversion en taux annuel
        $taeg = (pow(1 + $tauxMensuel, 12) - 1) * 100;

        return round($taeg, 2);
    }

    /**
     * Méthode pour générer les échéances
     */
    public function genererEcheances()
    {
        // Nettoyer les anciennes échéances si elles existent
        $this->echeances()->delete();

        $capitalRestant = $this->montant_emprunt;
        $interetsCumules = 0;
        $capitalCumule = 0;
        $assuranceCumulee = 0;
        $dateEcheance = $this->date_debut;

        // Taux périodique
        $tauxPeriodique = $this->taux_interet_annuel / 100;
        switch ($this->frequence_paiement) {
            case 'mensuel':
                $tauxPeriodique = $tauxPeriodique / 12;
                $interval = 'month';
                break;
            case 'trimestriel':
                $tauxPeriodique = $tauxPeriodique / 4;
                $interval = 'quarter';
                break;
            case 'annuel':
                $interval = 'year';
                break;
        }

        // Calcul de l'assurance mensuelle (si assurance annuelle, diviser par 12)
        $assuranceMensuelle = $this->frais_assurance > 0 ? $this->frais_assurance / $this->duree_mois : 0;

        for ($i = 1; $i <= $this->duree_mois; $i++) {
            if ($capitalRestant <= 0) break;

            // Ajuster la date pour la fréquence
            if ($i > 1) {
                $dateEcheance = $dateEcheance->copy()->add(1, $interval);
            }

            // Calculer les intérêts pour la période
            $interets = $capitalRestant * $tauxPeriodique;

            // Calculer le capital remboursé selon le type d'amortissement
            if ($this->type_amortissement === 'constant') {
                $capitalRembourse = $this->montant_mensualite - $interets;
            } else {
                $capitalRembourse = $this->montant_emprunt / $this->duree_mois;
            }

            // Ajuster pour la dernière échéance
            if ($capitalRestant - $capitalRembourse < 0) {
                $capitalRembourse = $capitalRestant;
            }

            // Calculer la mensualité réelle (avec assurance)
            $mensualiteTotale = $capitalRembourse + $interets + $assuranceMensuelle;

            // Mettre à jour les cumuls
            $interetsCumules += $interets;
            $capitalCumule += $capitalRembourse;
            $assuranceCumulee += $assuranceMensuelle;

            // Créer l'échéance
            $this->echeances()->create([
                'numero_echeance' => $i,
                'date_echeance' => $dateEcheance,
                'est_payee' => false,
                'capital_initial' => $capitalRestant,
                'montant_echeance' => $mensualiteTotale,
                'part_interets' => $interets,
                'part_capital' => $capitalRembourse,
                'part_assurance' => $assuranceMensuelle,
                'capital_restant' => $capitalRestant - $capitalRembourse,
                'interets_cumules' => $interetsCumules,
                'capital_cumule' => $capitalCumule,
                'assurance_cumulee' => $assuranceCumulee,
            ]);

            // Mettre à jour le capital restant
            $capitalRestant -= $capitalRembourse;
        }

        // Créer les frais détaillés
        $this->creerFraisDetail();
    }
    /**
     * 
     */
    private function creerFraisDetail()
    {
        $fraisTypes = [
            'dossier' => [
                'description' => 'Frais de dossier',
                'montant' => $this->frais_dossier,
            ],
            'assurance' => [
                'description' => 'Assurance emprunteur',
                'montant' => $this->frais_assurance,
            ],
            'notaire' => [
                'description' => 'Frais de notaire',
                'montant' => $this->frais_notaire,
            ],
            'autres' => [
                'description' => 'Autres frais',
                'montant' => $this->frais_autres,
            ],
        ];

        foreach ($fraisTypes as $type => $data) {
            if ($data['montant'] > 0) {
                $this->frais()->create([
                    'type' => $type,
                    'description' => $data['description'],
                    'montant' => $data['montant'],
                    'date_facturation' => now(),
                    'est_paye' => false,
                ]);
            }
        }
    }

    /**
     * Méthode pour vérifier si l'utilisateur peut faire une nouvelle demande
     */
    public static function utilisateurPeutEmprunter($userId): bool
    {
        $empruntsActifs = self::where('user_id', $userId)
            ->where('est_actif', true)
            ->whereIn('status', ['en_attente', 'approuve', 'en_cours'])
            ->count();

        return $empruntsActifs === 0;
    }

    /**
     * Méthode pour obtenir les emprunts actifs d'un utilisateur 
     */
    public static function getEmpruntActif($userId)
    {
        return self::where('user_id', $userId)
            ->where('est_actif', true)
            ->whereIn('status', ['en_attente', 'approuve', 'en_cours'])
            ->first();
    }

    /**
     * Accesseur pour la durée formatée
     */
    public function getDureeFormateeAttribute(): string
    {
        if ($this->duree_jours) {
            $jours = $this->duree_jours;
            $annees = floor($jours / 365);
            $mois = floor(($jours % 365) / 30);
            $joursRestants = $jours % 30;

            $parts = [];

            if ($annees > 0) {
                $parts[] = $annees . ' an' . ($annees > 1 ? 's' : '');
            }

            if ($mois > 0) {
                $parts[] = $mois . ' mois';
            }

            if ($joursRestants > 0) {
                $parts[] = $joursRestants . ' jour' . ($joursRestants > 1 ? 's' : '');
            }

            return implode(', ', $parts);
        }

        return $this->duree_mois . ' mois';
    }

    /**
     * Méthode pour calculer le taux mensuel
     */
    public function calculerTauxMensuel(): float
    {
        if (!$this->taux_interet_annuel) {
            return 0.0;
        }

        return round($this->taux_interet_annuel / 12, 3);
    }

    /**
     * Relation avec le conseiller
     */
    public function conseiller()
    {
        return $this->belongsTo(User::class, 'conseiller_id');
    }

    /**
     *  Méthode pour marquer comme notifié approuvé
     */
    public function marquerNotifieApprouve()
    {
        $this->update([
            'notifie_approuve' => true,
            'date_notification_approuve' => now(),
        ]);
    }

    /**
     * Méthode pour marquer comme notifié fonds disponibles
     */
    public function marquerNotifieFondsDisponibles()
    {
        $this->update([
            'notifie_fonds_disponibles' => true,
            'date_notification_fonds' => now(),
        ]);
    }

    /**
     *  Méthode pour vérifier si l'emprunt peut être payé (pour le client)
     */
    public function peutPayerEcheance(): bool
    {
        return $this->status === 'en_cours' && $this->est_actif;
    }

    /**
     * Méthode pour obtenir les notifications non lues (pour l'icône 🔔)
     */
    public function getNotificationsNonLuesAttribute()
    {
        if (!$this->user) {
            return collect();
        }

        return $this->user->unreadNotifications()
            ->where(function ($query) {
                $query->where('type', 'App\Notifications\EmpruntApprouve')
                    ->orWhere('type', 'App\Notifications\ArgentDisponible')
                    ->orWhere('type', 'App\Notifications\EcheanceProchaine');
            })
            ->get();
    }

    /**
     *  Modifier la méthode approuver pour inclure 
     * la désactivation des anciens emprunts
     */
    public function approuver()
    {
        /**
         * Désactiver les autres emprunts de l'utilisateur
         */
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->where('est_actif', true)
            ->update(['est_actif' => false]);

        $this->status = 'approuve';
        $this->date_approbation = now();
        $this->est_actif = true;

        // Calculer les totaux finaux
        $this->total_frais = $this->frais_dossier + $this->frais_assurance + $this->frais_notaire + $this->frais_autres;
        $this->montant_total_du = $this->total_a_rembourser + $this->total_frais;
        $this->taeg = $this->calculerTAEG();

        // Calculer le taux mensuel
        $this->taux_interet_mensuel = $this->calculerTauxMensuel();

        $this->save();

        // Générer les échéances
        $this->genererEcheances();

        return $this;
    }
}
