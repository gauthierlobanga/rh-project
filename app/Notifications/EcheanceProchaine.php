<?php

namespace App\Notifications;

use App\Models\Echeance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EcheanceProchaine extends Notification implements ShouldQueue
{
    use Queueable;

    public $echeance;

    public function __construct(Echeance $echeance)
    {
        $this->echeance = $echeance;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('📅 Échéance à venir')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Vous avez une échéance à payer bientôt.')
            ->line('**Détails :**')
            ->line('- Date : ' . $this->echeance->date_echeance->format('d/m/Y'))
            ->line('- Montant : ' . number_format($this->echeance->montant_echeance, 2, ',', ' ') . ' USD')
            ->line('- Échéance #' . $this->echeance->numero_echeance)
            ->action('Payer maintenant', url('/paiements/' . $this->echeance->id))
            ->line('Merci !');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Échéance à venir',
            'message' => 'Échéance #' . $this->echeance->numero_echeance . ' de ' . number_format($this->echeance->montant_echeance, 2, ',', ' ') . 'USD le ' . $this->echeance->date_echeance->format('d/m/Y'),
            'echeance_id' => $this->echeance->id,
            'emprunt_id' => $this->echeance->emprunt_id,
            'type' => 'echeance_prochaine',
            'action_url' => '/paiements/' . $this->echeance->id,
            'action_text' => 'Payer',
        ];
    }
}
