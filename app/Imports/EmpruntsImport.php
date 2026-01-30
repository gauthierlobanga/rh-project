<?php

namespace App\Imports;

use App\Models\Emprunt;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmpruntsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Calcul des valeurs dérivées
        $montantEmprunt = $row['montant_emprunt'] ?? $row['montant'] ?? 0;
        $dureeMois = $row['duree_mois'] ?? $row['duree'] ?? 12;
        $tauxAnnuel = $row['taux_interet_annuel'] ?? $row['taux'] ?? 5;
        $tauxMensuel = $tauxAnnuel / 12 / 100;

        // Calcul de la mensualité (formule simplifiée)
        if (($row['type_amortissement'] ?? 'constant') === 'constant') {
            $mensualite = ($montantEmprunt * $tauxMensuel) / (1 - pow(1 + $tauxMensuel, -$dureeMois));
        } else {
            $mensualite = $montantEmprunt / $dureeMois + ($montantEmprunt * $tauxMensuel);
        }

        $totalInterets = ($mensualite * $dureeMois) - $montantEmprunt;
        $totalARembourser = $montantEmprunt + $totalInterets;

        return new Emprunt([
            'nom_client' => $row['nom_client'] ?? $row['client'] ?? 'Client Importé',
            'montant_emprunt' => $montantEmprunt,
            'date_debut' => isset($row['date_debut'])
                ? Carbon::createFromFormat('Y-m-d', $row['date_debut'])
                : now(),
            'duree_mois' => $dureeMois,
            'taux_interet_annuel' => $tauxAnnuel,
            'taux_interet_mensuel' => $tauxMensuel * 100,
            'type_amortissement' => $row['type_amortissement'] ?? 'constant',
            'frequence_paiement' => $row['frequence_paiement'] ?? 'mensuel',
            'montant_mensualite' => round($mensualite, 2),
            'total_interets' => round($totalInterets, 2),
            'total_a_rembourser' => round($totalARembourser, 2),
            'status' => 'en_attente',
            'user_id' => Auth::id(),
            'date_fin_remboursement' => isset($row['date_debut'])
                ? Carbon::createFromFormat('Y-m-d', $row['date_debut'])->addMonths($dureeMois)
                : now()->addMonths($dureeMois),
            'reference' => 'IMP-' . strtoupper(uniqid()),
        ]);
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'nom_client' => 'required|string|max:255',
            'montant_emprunt' => 'required|numeric|min:100',
            'date_debut' => 'required|date',
            'duree_mois' => 'required|integer|min:1|max:360',
            'taux_interet_annuel' => 'required|numeric|min:0|max:50',
            'type_amortissement' => 'required|in:constant,decroissant,annuite_constante',
            'frequence_paiement' => 'required|in:mensuel,trimestriel,semestriel,annuel',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'nom_client.required' => 'Le nom du client est requis',
            'montant_emprunt.required' => 'Le montant de l\'emprunt est requis',
            'montant_emprunt.min' => 'Le montant minimum est 100',
            'date_debut.required' => 'La date de début est requise',
            'duree_mois.min' => 'La durée minimum est 1 mois',
            'duree_mois.max' => 'La durée maximum est 360 mois',
            'taux_interet_annuel.min' => 'Le taux d\'intérêt ne peut pas être négatif',
            'taux_interet_annuel.max' => 'Le taux d\'intérêt maximum est 50%',
        ];
    }
}
