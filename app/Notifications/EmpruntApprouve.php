<?php

namespace App\Notifications;

use App\Models\Emprunt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmpruntApprouve extends Notification implements ShouldQueue
{
    use Queueable;

    public $emprunt;

    public function __construct(Emprunt $emprunt)
    {
        $this->emprunt = $emprunt;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('✅ Votre emprunt a été approuvé !')
            ->greeting('Félicitations ' . $notifiable->name . ' !')
            ->line('Votre demande d\'emprunt a été approuvée par notre service.')
            ->line('**Détails :**')
            ->line('- Montant : ' . number_format($this->emprunt->montant_emprunt, 0, ',', ' ') . ' USD')
            ->line('- Taux : ' . $this->emprunt->taux_interet_annuel . '%')
            ->line('- Mensualité : ' . number_format($this->emprunt->montant_mensualite, 2, ',', ' ') . ' USD')
            ->line('**Prochaine étape :**')
            ->line('Rendez-vous à votre agence pour signer le contrat et récupérer les fonds.')
            ->action('Voir les détails', url('/mes-emprunts/' . $this->emprunt->id))
            ->line('Merci de votre confiance !');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Emprunt approuvé',
            'message' => 'Votre emprunt de ' . number_format($this->emprunt->montant_emprunt, 0, ',', ' ') . 'USD a été approuvé. Taux : ' . $this->emprunt->taux_interet_annuel . '%.',
            'emprunt_id' => $this->emprunt->id,
            'type' => 'emprunt_approuve',
            'action_url' => '/mes-emprunts/' . $this->emprunt->id,
            'action_text' => 'Voir le contrat',
        ];
    }
}
