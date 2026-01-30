<?php

// namespace App\Livewire\Forms;

// use Livewire\Form;
// use App\Models\Emprunt;
// use Illuminate\Support\Facades\Auth;

// class EmpruntForms extends Form
// {

//     public function store($data)
//     {
//         $userId = Auth::id();

//         // Calculer la durée en mois
//         $dateDebut = \Carbon\Carbon::parse($data['date_debut']);
//         $dateFin = \Carbon\Carbon::parse($data['date_fin_remboursement']);
//         $dureeMois = $dateDebut->diffInMonths($dateFin);

//         // Calculer le taux périodique selon la fréquence
//         $tauxPeriodique = $data['taux_interet_annuel'] / 100;

//         switch ($data['frequence_paiement']) {
//             case 'mensuel':
//                 $tauxPeriodique = $tauxPeriodique / 12;
//                 $periodes = $dureeMois;
//                 break;
//             case 'trimestriel':
//                 $tauxPeriodique = $tauxPeriodique / 4;
//                 $periodes = $dureeMois / 3;
//                 break;
//             case 'annuel':
//                 $periodes = $dureeMois / 12;
//                 break;
//         }

//         // Calculer la mensualité selon le type d'amortissement
//         $montantEmprunt = $data['montant_emprunt'];

//         if ($data['type_amortissement'] === 'constant') {
//             // Formule des annuités constantes
//             $mensualite = $montantEmprunt * ($tauxPeriodique / (1 - pow(1 + $tauxPeriodique, -$periodes)));
//         } else {
//             // Amortissement décroissant
//             $amortissementCapital = $montantEmprunt / $periodes;
//             $mensualite = $amortissementCapital + ($montantEmprunt * $tauxPeriodique);
//         }

//         // Calculer les totaux
//         $totalInterets = ($mensualite * $periodes) - $montantEmprunt;
//         $totalARembourser = $montantEmprunt + $totalInterets;

//         // Calculer les frais totaux
//         $totalFrais = ($data['frais_dossier_estime'] ?? 0) + ($data['frais_assurance_estime'] ?? 0) +
//             ($data['frais_notaire_estime'] ?? 0) + ($data['frais_autres_estime'] ?? 0);

//         $montantTotalDu = $totalARembourser + $totalFrais;

//         // Créer l'emprunt avec l'utilisateur connecté
//         $emprunt = Emprunt::create([
//             'user_id' => $userId,
//             'montant_emprunt' => $montantEmprunt,
//             'date_fin_remboursement' => $data['date_fin_remboursement'],
//             'taux_interet_annuel' => $data['taux_interet_annuel'],
//             'type_amortissement' => $data['type_amortissement'],
//             'frequence_paiement' => $data['frequence_paiement'],
//             'date_debut' => $data['date_debut'],
//             'duree_mois' => $dureeMois,
//             // Frais estimés
//             'frais_dossier' => $data['frais_dossier_estime'] ?? 0,
//             'frais_assurance' => $data['frais_assurance_estime'] ?? 0,
//             'frais_notaire' => $data['frais_notaire_estime'] ?? 0,
//             'frais_autres' => $data['frais_autres_estime'] ?? 0,
//             // Calculs
//             'montant_mensualite' => $mensualite,
//             'total_interets' => $totalInterets,
//             'total_a_rembourser' => $totalARembourser,
//             'total_frais' => $totalFrais,
//             'montant_total_du' => $montantTotalDu,
//             'taeg' => null,
//             'status' => 'en_attente',
//             'notes' => $data['notes'] ?? '',
//         ]);

//         return $emprunt;
//     }

//     // Méthode pour calculer le TAEG estimé
//     public function calculerTAEGEstime($montantEmprunt, $mensualite, $dureeMois, $totalFrais)
//     {
//         if ($montantEmprunt <= 0 || $totalFrais >= $montantEmprunt) {
//             return 0;
//         }

//         $capitalEffectif = $montantEmprunt - $totalFrais;
//         $tauxMensuel = 0.01;
//         $precision = 0.000001;
//         $maxIterations = 1000;

//         for ($i = 0; $i < $maxIterations; $i++) {
//             $calcul = $mensualite * (1 - pow(1 + $tauxMensuel, -$dureeMois)) / $tauxMensuel;
//             $erreur = $calcul - $capitalEffectif;

//             if (abs($erreur) < $precision) {
//                 break;
//             }

//             $tauxMensuel = $tauxMensuel - ($erreur / ($capitalEffectif * 100));
//         }

//         $taeg = (pow(1 + $tauxMensuel, 12) - 1) * 100;

//         return round($taeg, 2);
//     }
// }

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Emprunt;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Livewire\Attributes\Validate;

class EmpruntForm extends Form
{
    #[Validate]
    public $montant_emprunt;
    #[Validate]
    public $date_fin_remboursement;
    #[Validate]
    public $type_amortissement = 'constant';
    #[Validate]
    public $frequence_paiement = 'mensuel';
    #[Validate]
    public $date_debut;
    #[Validate]
    public $notes = '';

    public function rules()
    {
        return [
            'montant_emprunt' => ['required', 'numeric', 'min:1000', 'max:1000000'],
            'date_fin_remboursement' => ['required', 'date', 'after:date_debut'],
            'type_amortissement' => ['required', Rule::in(['constant', 'decroissant'])],
            'frequence_paiement' => ['required', Rule::in(['mensuel', 'trimestriel', 'annuel'])],
            'date_debut' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['required', 'string', 'max:500'], // Rendre obligatoire
        ];
    }

    public function messages()
    {
        return [
            'montant_emprunt.required' => 'Le montant de l\'emprunt est requis.',
            'montant_emprunt.min' => 'Le montant minimum est de 1000USD.',
            'montant_emprunt.max' => 'Le montant maximum est de 1,000,000USD.',
            'date_fin_remboursement.required' => 'La date de fin de remboursement est requise.',
            'date_fin_remboursement.after' => 'La date de fin doit être après la date de début.',
            'date_debut.required' => 'La date de début est requise.',
            'date_debut.after_or_equal' => 'La date de début ne peut pas être dans le passé.',
            'notes.required' => 'Veuillez indiquer le motif de l\'emprunt.',
        ];
    }

    public function store()
    {
        $this->validate();

        // Vérifier si l'utilisateur peut faire un nouvel emprunt
        if (!Emprunt::utilisateurPeutEmprunter(Auth::id())) {
            $empruntActif = Emprunt::getEmpruntActif(Auth::id());
            $message = match ($empruntActif->status) {
                'en_attente' => 'Vous avez déjà une demande d\'emprunt en attente de traitement.',
                'approuve' => 'Vous avez un emprunt approuvé en attente de signature.',
                'en_cours' => 'Vous avez déjà un emprunt en cours de remboursement.',
                default => 'Vous ne pouvez pas faire une nouvelle demande d\'emprunt.'
            };
            throw new \Exception($message);
        }

        $userId = Auth::id();

        // Calculer la durée en mois et jours
        $dateDebut = Carbon::parse($this->date_debut);
        $dateFin = Carbon::parse($this->date_fin_remboursement);
        $dureeMois = $dateDebut->diffInMonths($dateFin);
        $dureeJours = $dateDebut->diffInDays($dateFin);

        // Créer l'emprunt sans taux (sera défini par la banque)
        $emprunt = Emprunt::create([
            'user_id' => $userId,
            'montant_emprunt' => $this->montant_emprunt,
            'date_fin_remboursement' => $this->date_fin_remboursement,
            'type_amortissement' => $this->type_amortissement,
            'frequence_paiement' => $this->frequence_paiement,
            'date_debut' => $this->date_debut,
            'duree_mois' => $dureeMois,
            'duree_jours' => $dureeJours,
            // Taux laissés à null - seront définis par la banque
            'taux_interet_annuel' => null,
            'taux_interet_mensuel' => null,
            // Pas de calculs pour l'instant
            'montant_mensualite' => null,
            'total_interets' => null,
            'total_a_rembourser' => null,
            'total_frais' => null,
            'montant_total_du' => null,
            'taeg' => null,
            // Statut
            'status' => 'en_attente',
            'notes' => $this->notes,
            'est_actif' => true,
            // Frais (seront définis par la banque)
            'frais_dossier' => 0,
            'frais_assurance' => 0,
            'frais_notaire' => 0,
            'frais_autres' => 0,
        ]);

        return $emprunt;
    }

    /**
     * Méthode pour calculer le TAEG estimé
     */
    public function calculerTAEGEstime($montantEmprunt, $mensualite, $dureeMois, $totalFrais)
    {
        if ($montantEmprunt <= 0 || $totalFrais >= $montantEmprunt) {
            return 0;
        }

        $capitalEffectif = $montantEmprunt - $totalFrais;
        $tauxMensuel = 0.01;
        $precision = 0.000001;
        $maxIterations = 1000;

        for ($i = 0; $i < $maxIterations; $i++) {
            $calcul = $mensualite * (1 - pow(1 + $tauxMensuel, -$dureeMois)) / $tauxMensuel;
            $erreur = $calcul - $capitalEffectif;

            if (abs($erreur) < $precision) {
                break;
            }

            $tauxMensuel = $tauxMensuel - ($erreur / ($capitalEffectif * 100));
        }

        $taeg = (pow(1 + $tauxMensuel, 12) - 1) * 100;

        return round($taeg, 2);
    }
}
