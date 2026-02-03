<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\ContratAssuranceVie;
use App\Models\Cotisation;
use App\Models\NotificationAssurance;
use App\Models\Sinistre;

class NotificationService
{
    /**
     * Envoyer un rappel de paiement
     */
    public function sendPaymentReminder(Cotisation $cotisation): NotificationAssurance
    {
        $user = $cotisation->contrat->souscripteur->utilisateur;

        $notification = NotificationAssurance::create([
            'destinataire_id' => $user->id,
            'type_notification' => 'rappel_paiement',
            'titre' => 'Rappel de paiement',
            'contenu' => "Votre cotisation de {$cotisation->montant_due} € est due le {$cotisation->date_echeance->format('d/m/Y')}.",
            'donnees_liees' => [
                'cotisation_id' => $cotisation->id,
                'montant' => $cotisation->montant_due,
                'date_echeance' => $cotisation->date_echeance,
                'contrat_numero' => $cotisation->contrat->numero_contrat,
            ],
            'canal_envoi' => 'tous',
            'est_urgente' => $cotisation->date_echeance->diffInDays(now()) <= 7,
        ]);

        // Envoyer immédiatement
        $notification->envoyer();

        return $notification;
    }

    /**
     * Notifier l'activation d'un contrat
     */
    public function sendContractActivatedNotification(ContratAssuranceVie $contrat): NotificationAssurance
    {
        $user = $contrat->souscripteur->utilisateur;
        $agent = $contrat->agent->utilisateur;

        // Notification au client
        $clientNotification = NotificationAssurance::create([
            'destinataire_id' => $user->id,
            'type_notification' => 'contrat_active',
            'titre' => 'Votre contrat est activé',
            'contenu' => "Votre contrat {$contrat->numero_contrat} a été activé avec succès.",
            'donnees_liees' => [
                'contrat_id' => $contrat->id,
                'numero_contrat' => $contrat->numero_contrat,
                'capital_assure' => $contrat->capital_assure,
                'date_effet' => $contrat->date_effet,
            ],
            'canal_envoi' => 'email',
        ]);

        // Notification à l'agent
        $agentNotification = NotificationAssurance::create([
            'destinataire_id' => $agent->id,
            'type_notification' => 'contrat_active',
            'titre' => 'Contrat activé',
            'contenu' => "Le contrat {$contrat->numero_contrat} de {$contrat->souscripteur->utilisateur->name} a été activé.",
            'donnees_liees' => [
                'contrat_id' => $contrat->id,
                'client_id' => $contrat->souscripteur_id,
                'commission_estimee' => $contrat->prime_annuelle * 0.05,
            ],
            'canal_envoi' => 'application',
        ]);

        $clientNotification->envoyer();
        $agentNotification->envoyer();

        return $clientNotification;
    }

    /**
     * Notifier un sinistre déclaré
     */
    public function sendClaimDeclaredNotification(Sinistre $sinistre): void
    {
        $client = $sinistre->contrat->souscripteur->utilisateur;
        $agent = $sinistre->contrat->agent->utilisateur;

        // Notification au client
        NotificationAssurance::create([
            'destinataire_id' => $client->id,
            'type_notification' => 'sinistre_declare',
            'titre' => 'Sinistre enregistré',
            'contenu' => "Votre sinistre {$sinistre->numero_sinistre} a été enregistré et est en cours de traitement.",
            'donnees_liees' => [
                'sinistre_id' => $sinistre->id,
                'numero_sinistre' => $sinistre->numero_sinistre,
                'type_sinistre' => $sinistre->type_sinistre,
                'montant_reclame' => $sinistre->montant_reclame,
            ],
            'canal_envoi' => 'tous',
        ])->envoyer();

        // Notification à l'agent
        NotificationAssurance::create([
            'destinataire_id' => $agent->id,
            'type_notification' => 'sinistre_declare',
            'titre' => 'Nouveau sinistre déclaré',
            'contenu' => "Un sinistre {$sinistre->type_sinistre} a été déclaré pour le contrat {$sinistre->contrat->numero_contrat}.",
            'donnees_liees' => [
                'sinistre_id' => $sinistre->id,
                'contrat_id' => $sinistre->contrat_id,
                'client' => $sinistre->contrat->souscripteur->nom_complet,
            ],
            'canal_envoi' => 'application',
        ])->envoyer();
    }

    /**
     * Notifier une commission calculée
     */
    public function sendCommissionCalculatedNotification(Commission $commission): NotificationAssurance
    {
        $agent = $commission->agent->utilisateur;

        $notification = NotificationAssurance::create([
            'destinataire_id' => $agent->id,
            'type_notification' => 'commission_calculee',
            'titre' => 'Nouvelle commission',
            'contenu' => "Une commission de {$commission->montant_commission} € a été calculée pour le contrat {$commission->contrat->numero_contrat}.",
            'donnees_liees' => [
                'commission_id' => $commission->id,
                'montant' => $commission->montant_commission,
                'type' => $commission->type_commission,
                'contrat_numero' => $commission->contrat->numero_contrat,
                'date_calcul' => $commission->date_calcul,
            ],
            'canal_envoi' => 'application',
        ]);

        $notification->envoyer();

        return $notification;
    }

    /**
     * Envoyer des rappels batch
     */
    public function sendBatchReminders(): array
    {
        $results = [
            'sent' => 0,
            'errors' => [],
        ];

        // Cotisations dues dans 7 jours
        $dueSoon = Cotisation::where('statut_paiement', 'en_attente')
            ->whereBetween('date_echeance', [now(), now()->addDays(7)])
            ->where('est_rappele', false)
            ->with('contrat.souscripteur.utilisateur')
            ->get();

        foreach ($dueSoon as $cotisation) {
            try {
                $this->sendPaymentReminder($cotisation);
                $cotisation->update(['est_rappele' => true]);
                $results['sent']++;
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'cotisation_id' => $cotisation->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Marquer les notifications comme lues
     */
    public function markAsRead(array $notificationIds, int $userId): int
    {
        return NotificationAssurance::whereIn('id', $notificationIds)
            ->where('destinataire_id', $userId)
            ->where('est_lue', false)
            ->update([
                'est_lue' => true,
                'date_lecture' => now(),
            ]);
    }

    /**
     * Récupérer les notifications non lues
     */
    public function getUnreadNotifications(int $userId, int $limit = 10)
    {
        return NotificationAssurance::where('destinataire_id', $userId)
            ->where('est_lue', false)
            ->where('date_expiration', '>', now())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
