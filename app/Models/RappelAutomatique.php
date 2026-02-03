<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RappelAutomatique extends Model
{
    protected $table = 'rappels_automatiques';

    protected $fillable = [
        'type_rappel',
        'sujet_rappel',
        'description',
        'destinataire_id',
        'destinataire_type',
        'mode_envoi',
        'statut_envoi',
        'date_programmation',
        'date_envoi',
        'date_reception',
        'frequence_rappel',
        'nombre_tentatives',
        'max_tentatives',
        'delai_entre_tentatives',
        'canaux_envoi',
        'modeles_utilises',
        'parametres_envoi',
        'reponse_destinataire',
        'erreurs_envoi',
        'est_urgent',
        'priorite',
        'tags',
        'campagne_id',
        'lien_element',
    ];

    protected $casts = [
        'date_programmation' => 'datetime',
        'date_envoi' => 'datetime',
        'date_reception' => 'datetime',
        'canaux_envoi' => 'array',
        'modeles_utilises' => 'array',
        'parametres_envoi' => 'array',
        'reponse_destinataire' => 'array',
        'erreurs_envoi' => 'array',
        'tags' => 'array',
        'est_urgent' => 'boolean',
        'delai_entre_tentatives' => 'integer',
        'max_tentatives' => 'integer',
        'nombre_tentatives' => 'integer',
    ];

    // Relations polymorphiques pour différents types de destinataires
    public function destinataire(): MorphTo
    {
        return $this->morphTo();
    }

    // Relations spécifiques
    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContratAssuranceVie::class, 'lien_element');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'lien_element');
    }

    // Accesseurs
    public function getEstProgrammeAttribute(): bool
    {
        return $this->statut_envoi === 'programme';
    }

    public function getEstEnvoyeAttribute(): bool
    {
        return $this->statut_envoi === 'envoye';
    }

    public function getEstEchoueAttribute(): bool
    {
        return $this->statut_envoi === 'echoue';
    }

    public function getEstReceptionneAttribute(): bool
    {
        return $this->statut_envoi === 'receptionne';
    }

    public function getEstEnRetardAttribute(): bool
    {
        return $this->date_programmation < now() && $this->statut_envoi === 'programme';
    }

    public function getJoursRestantsAttribute(): ?int
    {
        if ($this->date_programmation) {
            return now()->diffInDays($this->date_programmation, false);
        }

        return null;
    }

    // Scopes
    public function scopeProgrammes($query)
    {
        return $query->where('statut_envoi', 'programme');
    }

    public function scopeEnvoyes($query)
    {
        return $query->where('statut_envoi', 'envoye');
    }

    public function scopeEchoues($query)
    {
        return $query->where('statut_envoi', 'echoue');
    }

    public function scopeUrgents($query)
    {
        return $query->where('est_urgent', true);
    }

    public function scopeEnRetard($query)
    {
        return $query->where('statut_envoi', 'programme')
            ->where('date_programmation', '<', now());
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type_rappel', $type);
    }

    public function scopeParDestinataire($query, $destinataireId, $destinataireType)
    {
        return $query->where('destinataire_id', $destinataireId)
            ->where('destinataire_type', $destinataireType);
    }

    // Méthodes métier
    public function programmer(array $options = []): bool
    {
        $this->update([
            'statut_envoi' => 'programme',
            'date_programmation' => $options['date'] ?? now()->addDay(),
            'mode_envoi' => $options['mode'] ?? 'email',
            'priorite' => $options['priorite'] ?? 'normal',
        ]);

        return true;
    }

    public function envoyer(): bool
    {
        try {
            // Logique d'envoi selon le mode
            $canaux = $this->canaux_envoi ?? ['email'];

            foreach ($canaux as $canal) {
                $this->envoyerViaCanal($canal);
            }

            $this->update([
                'statut_envoi' => 'envoye',
                'date_envoi' => now(),
                'nombre_tentatives' => $this->nombre_tentatives + 1,
            ]);

            return true;
        } catch (\Exception $e) {
            $this->update([
                'statut_envoi' => 'echoue',
                'erreurs_envoi' => array_merge($this->erreurs_envoi ?? [], [
                    'tentative_'.($this->nombre_tentatives + 1) => [
                        'date' => now(),
                        'erreur' => $e->getMessage(),
                    ],
                ]),
                'nombre_tentatives' => $this->nombre_tentatives + 1,
            ]);

            // Réessayer si pas atteint le maximum
            if ($this->nombre_tentatives < $this->max_tentatives) {
                $this->reprogrammer();
            }

            return false;
        }
    }

    private function envoyerViaCanal(string $canal): void
    {
        switch ($canal) {
            case 'email':
                $this->envoyerEmail();
                break;
            case 'sms':
                $this->envoyerSMS();
                break;
            case 'notification':
                $this->envoyerNotification();
                break;
            case 'courrier':
                $this->envoyerCourrier();
                break;
        }
    }

    private function envoyerEmail(): void
    {
        // Logique d'envoi d'email
        $destinataire = $this->destinataire;
        $modele = $this->modeles_utilises['email'] ?? 'default';

        // Utiliser un template d'email avec les paramètres
        // Mail::to($destinataire->email)->send(new RappelEmail($this));
    }

    private function envoyerSMS(): void
    {
        // Logique d'envoi SMS
        $destinataire = $this->destinataire;
        $message = $this->genererMessageSMS();

        // Envoyer via service SMS
        // SMSService::send($destinataire->telephone, $message);
    }

    private function envoyerNotification(): void
    {
        // Logique de notification interne
        NotificationAssurance::create([
            'destinataire_id' => $this->destinataire->id,
            'type_notification' => 'rappel_automatique',
            'titre' => $this->sujet_rappel,
            'contenu' => $this->description,
            'donnees_liees' => ['rappel_id' => $this->id],
            'canal_envoi' => 'application',
        ]);
    }

    private function envoyerCourrier(): void
    {
        // Logique d'envoi courrier postal
        // Générer PDF et envoyer par courrier
    }

    public function reprogrammer(): void
    {
        $delai = $this->delai_entre_tentatives ?? 24; // 24 heures par défaut
        $nouvelleDate = now()->addHours($delai);

        $this->update([
            'statut_envoi' => 'programme',
            'date_programmation' => $nouvelleDate,
        ]);
    }

    public function confirmerReception(array $details = []): bool
    {
        $this->update([
            'statut_envoi' => 'receptionne',
            'date_reception' => now(),
            'reponse_destinataire' => $details,
        ]);

        return true;
    }

    public function genererMessageSMS(): string
    {
        $type = $this->type_rappel;

        $messages = [
            'echeance_paiement' => "Rappel: Votre prime d'assurance vie est due le {$this->date_programmation->format('d/m/Y')}. Montant: {$this->parametres_envoi['montant']}€",
            'anniversaire_contrat' => "Bon anniversaire! Votre contrat fête ses {$this->parametres_envoi['annees']} ans aujourd'hui.",
            'document_manquant' => 'Des documents sont manquants pour votre dossier. Merci de nous les transmettre.',
            'rappel_rdv' => "Rappel de rendez-vous le {$this->date_programmation->format('d/m/Y H:i')} avec {$this->parametres_envoi['agent']}",
        ];

        return $messages[$type] ?? $this->description;
    }

    public function analyserPerformance(): array
    {
        return [
            'statut' => $this->statut_envoi,
            'tentatives' => $this->nombre_tentatives,
            'dernier_envoi' => $this->date_envoi,
            'reception' => $this->date_reception,
            'temps_reponse' => $this->date_reception
                ? $this->date_envoi->diffInHours($this->date_reception)
                : null,
            'erreurs' => count($this->erreurs_envoi ?? []),
            'reponse' => $this->reponse_destinataire ? 'oui' : 'non',
        ];
    }
}
