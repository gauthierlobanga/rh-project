<?php

// namespace App\Filament\Widgets;

// use App\Models\Emprunt;
// use App\Models\Echeance;
// use Filament\Widgets\StatsOverviewWidget;
// use Filament\Widgets\StatsOverviewWidget\Stat;
// use Illuminate\Support\Facades\DB;

// class EmpruntOverview extends StatsOverviewWidget
// {
//     public function getStats(): array
//     {
//         $totalEmprunts = Emprunt::count();
//         $totalMontant = Emprunt::sum('montant_emprunt');
//         $totalEnCours = Emprunt::where('status', 'en_cours')->count();
//         $totalTermine = Emprunt::where('status', 'termine')->count();

//         // Calcul des intérêts perçus
//         $interetsPerçus = Echeance::where('est_payee', true)->sum('part_interets');

//         // Taux de défaut
//         $totalDefaut = Emprunt::where('status', 'defaut')->count();
//         $tauxDefaut = $totalEmprunts > 0 ? round(($totalDefaut / $totalEmprunts) * 100, 2) : 0;

//         return [
//             Stat::make('Total emprunts', number_format($totalEmprunts, 0, ',', ' '))
//                 ->description('Tous les emprunts créés')
//                 ->descriptionIcon('heroicon-m-banknotes')
//                 ->color('primary'),

//             Stat::make('Capital prêté', number_format($totalMontant, 0, ',', ' '))
//                 ->description('Montant total octroyé')
//                 ->descriptionIcon('heroicon-m-currency-euro')
//                 ->color('success'),

//             Stat::make('Emprunts en cours', number_format($totalEnCours, 0, ',', ' '))
//                 ->description('Actuellement en remboursement')
//                 ->descriptionIcon('heroicon-m-clock')
//                 ->color('warning'),

//             Stat::make('Emprunts terminés', number_format($totalTermine, 0, ',', ' '))
//                 ->description('Intégralement remboursés')
//                 ->descriptionIcon('heroicon-m-check-circle')
//                 ->color('success'),

//             Stat::make('Intérêts', number_format($interetsPerçus, 0, ',', ' '))
//                 ->description('Revenus d\'intérêts')
//                 ->descriptionIcon('heroicon-m-chart-bar')
//                 ->color('info'),

//             Stat::make('Taux', $tauxDefaut . ' %')
//                 ->description('Prêts en défaut de paiement')
//                 ->descriptionIcon($tauxDefaut > 5 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
//                 ->color($tauxDefaut > 5 ? 'danger' : 'success'),
//             // ajouter le stat pour le taux de defaut
//             Stat::make('Taux', $tauxDefaut . ' %')
//                 ->description('Prêts en défaut de paiement')
//                 ->descriptionIcon($tauxDefaut > 5 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
//                 ->color($tauxDefaut > 5 ? 'danger' : 'success'),
//             Stat::make('Taux', $tauxDefaut . ' %')
//                 ->description('Prêts en défaut de paiement')
//                 ->descriptionIcon($tauxDefaut > 5 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
//                 ->color($tauxDefaut > 5 ? 'danger' : 'success'),
//         ];
//     }
// }


namespace App\Filament\Widgets;

use App\Models\Emprunt;
use App\Models\Echeance;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmpruntOverview extends StatsOverviewWidget
{
    public function getStats(): array
    {
        // Récupérer l'utilisateur connecté
        $user = Auth::user();
        $userId = $user->id;

        // Filtrer tous les calculs par l'utilisateur connecté
        $totalEmprunts = Emprunt::where('user_id', $userId)->count();
        $totalMontant = Emprunt::where('user_id', $userId)->sum('montant_emprunt');
        $totalEnCours = Emprunt::where('user_id', $userId)->where('status', 'en_cours')->count();
        $totalTermine = Emprunt::where('user_id', $userId)->where('status', 'termine')->count();

        // Calcul des intérêts perçus pour l'utilisateur
        $interetsPerçus = Echeance::whereHas('emprunt', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->where('est_payee', true)
            ->sum('part_interets');

        // Taux de défaut pour l'utilisateur
        $totalDefaut = Emprunt::where('user_id', $userId)->where('status', 'defaut')->count();
        $tauxDefaut = $totalEmprunts > 0 ? round(($totalDefaut / $totalEmprunts) * 100, 2) : 0;

        return [
            Stat::make('Mes emprunts', number_format($totalEmprunts, 0, ',', ' '))
                ->description('Tous mes emprunts')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('blue'),

            Stat::make('Capital emprunté', number_format($totalMontant, 0, ',', ' ') . ' USD')
                ->description('Total que j\'ai emprunté')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('green'),

            Stat::make('Emprunts en cours', number_format($totalEnCours, 0, ',', ' '))
                ->description('Mes emprunts en remboursement')
                ->descriptionIcon('heroicon-m-clock')
                ->color('yellow'),

            Stat::make('Emprunts terminés', number_format($totalTermine, 0, ',', ' '))
                ->description('Mes emprunts remboursés')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('green'),

            Stat::make('Intérêts payés', number_format($interetsPerçus, 0, ',', ' ') . ' USD')
                ->description('Intérêts que j\'ai payés')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Mes retards', $tauxDefaut . ' %')
                ->description('Mes emprunts en retard')
                ->descriptionIcon($tauxDefaut > 5 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($tauxDefaut > 5 ? 'red' : 'green'),

            Stat::make('Intérêts payés', number_format($interetsPerçus, 0, ',', ' ') . ' USD')
                ->description('Intérêts que j\'ai payés')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('blue'),

            Stat::make('Mes retards', $tauxDefaut . ' %')
                ->description('Mes emprunts en retard')
                ->descriptionIcon($tauxDefaut > 5 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($tauxDefaut > 5 ? 'red' : 'green'),
        ];
    }
}
