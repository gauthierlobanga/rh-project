<?php

namespace App\Exports;

use App\Models\Emprunt;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmpruntsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $collection;
    protected $columns;

    public function __construct($collection = null, $columns = [])
    {
        $this->collection = $collection ?: Emprunt::where('user_id', Auth::id())->get();
        $this->columns = $columns ?: [
            'reference',
            'nom_client',
            'montant_emprunt',
            'date_debut',
            'date_fin_remboursement',
            'duree_mois',
            'taux_interet_annuel',
            'type_amortissement',
            'frequence_paiement',
            'montant_mensualite',
            'total_interets',
            'total_a_rembourser',
            'status'
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->collection;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $headingsMap = [
            'reference' => 'Référence',
            'nom_client' => 'Nom du Client',
            'montant_emprunt' => 'Montant Emprunté (USD)',
            'date_debut' => 'Date Début',
            'date_fin_remboursement' => 'Date Fin Remboursement',
            'duree_mois' => 'Durée (Mois)',
            'taux_interet_annuel' => 'Taux Annuel (%)',
            'type_amortissement' => 'Type Amortissement',
            'frequence_paiement' => 'Fréquence Paiement',
            'montant_mensualite' => 'Mensualité (FCFA)',
            'total_interets' => 'Total Intérêts (FCFA)',
            'total_a_rembourser' => 'Total à Rembourser (USD)',
            'status' => 'Statut',
        ];

        $headings = [];
        foreach ($this->columns as $column) {
            if (isset($headingsMap[$column])) {
                $headings[] = $headingsMap[$column];
            }
        }

        return $headings;
    }

    /**
     * @param Emprunt $emprunt
     * @return array
     */
    public function map($emprunt): array
    {
        $row = [];

        foreach ($this->columns as $column) {
            switch ($column) {
                case 'reference':
                    $row[] = $emprunt->reference;
                    break;
                case 'nom_client':
                    $row[] = $emprunt->user->name;
                    break;
                case 'montant_emprunt':
                    $row[] = number_format($emprunt->montant_emprunt, 0, ',', ' ');
                    break;
                case 'date_debut':
                    $row[] = $emprunt->date_debut->format('d/m/Y');
                    break;
                case 'date_fin_remboursement':
                    $row[] = $emprunt->date_fin_remboursement->format('d/m/Y');
                    break;
                case 'duree_mois':
                    $row[] = $emprunt->duree_mois;
                    break;
                case 'taux_interet_annuel':
                    $row[] = number_format($emprunt->taux_interet_annuel, 2, ',', ' ') . '%';
                    break;
                case 'type_amortissement':
                    $row[] = ucfirst(str_replace('_', ' ', $emprunt->type_amortissement));
                    break;
                case 'frequence_paiement':
                    $row[] = ucfirst($emprunt->frequence_paiement);
                    break;
                case 'montant_mensualite':
                    $row[] = number_format($emprunt->montant_mensualite, 0, ',', ' ');
                    break;
                case 'total_interets':
                    $row[] = number_format($emprunt->total_interets, 0, ',', ' ');
                    break;
                case 'total_a_rembourser':
                    $row[] = number_format($emprunt->total_a_rembourser, 0, ',', ' ');
                    break;
                case 'status':
                    $statusMap = [
                        'en_attente' => 'En Attente',
                        'approuve' => 'Approuvé',
                        'rejete' => 'Rejeté',
                        'termine' => 'Terminé',
                        'defaut' => 'Défaut',
                        'annule' => 'Annulé',
                    ];
                    $row[] = $statusMap[$emprunt->status] ?? $emprunt->status;
                    break;
                default:
                    $row[] = $emprunt->{$column} ?? '';
            }
        }

        return $row;
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style pour l'en-tête
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'], // Couleur indigo
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Style pour les colonnes numériques
            'C' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
            'J' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
            'K' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
            'L' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15, // Référence
            'B' => 25, // Nom Client
            'C' => 20, // Montant
            'D' => 15, // Date Début
            'E' => 20, // Date Fin
            'F' => 15, // Durée
            'G' => 15, // Taux
            'H' => 20, // Type Amortissement
            'I' => 18, // Fréquence
            'J' => 18, // Mensualité
            'K' => 18, // Total Intérêts
            'L' => 20, // Total à Rembourser
            'M' => 15, // Statut
        ];
    }
}
