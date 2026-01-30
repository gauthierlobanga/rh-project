<?php

namespace App\Notifications;

use App\Models\Emprunt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArgentDisponible extends Notification implements ShouldQueue
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
            ->subject('💰 Les fonds sont disponibles !')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Les fonds de votre emprunt sont maintenant disponibles.')
            ->line('**Montant :** ' . number_format($this->emprunt->montant_emprunt, 0, ',', ' ') . ' USD')
            ->line('**Référence :** EMP-' . str_pad($this->emprunt->id, 6, '0', STR_PAD_LEFT))
            ->line('Les fonds ont été virés sur votre compte.')
            ->action('Consulter', url('/mes-emprunts/' . $this->emprunt->id))
            ->line('Merci de votre confiance !');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Fonds disponibles',
            'message' => 'Les fonds de votre emprunt (' . number_format($this->emprunt->montant_emprunt, 0, ',', ' ') . 'USD) sont disponibles.',
            'emprunt_id' => $this->emprunt->id,
            'type' => 'argent_disponible',
            'action_url' => '/mes-emprunts/' . $this->emprunt->id,
            'action_text' => 'Vérifier',
        ];
    }
}
