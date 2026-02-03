<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationAssurance extends Model
{
    protected $table = 'notification_assurances';

    protected $fillable = [
        'destinataire_id',
        'type_notification',
        'titre',
        'contenu',
        'donnees_liees',
        'est_lue',
        'date_lecture',
        'canal_envoi',
        'est_envoyee',
        'date_envoi',
        'erreur_envoi',
        'tentatives_envoi',
        'est_urgente',
        'date_expiration'
    ];

    protected $casts = [
        'donnees_liees' => 'array',
        'est_lue' => 'boolean',
        'est_envoyee' => 'boolean',
        'est_urgente' => 'boolean',
        'date_lecture' => 'datetime',
        'date_envoi' => 'datetime',
        'date_expiration' => 'date'
    ];

    // Relations
    public function destinataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    // Accesseurs
    public function getEstExpireeAttribute(): bool
    {
        return $this->date_expiration && $this->date_expiration < now();
    }

    public function getCouleurAttribute(): string
    {
        return match ($this->type_notification) {
            'rappel_paiement', 'echeance_proche' => 'warning',
            'sinistre_declare', 'alerte_securite' => 'danger',
            'contrat_active', 'commission_calculee' => 'success',
            default => 'info',
        };
    }

    // Scopes
    public function scopeNonLues($query)
    {
        return $query->where('est_lue', false);
    }

    public function scopeUrgentes($query)
    {
        return $query->where('est_urgente', true);
    }

    public function scopePourUtilisateur($query, $userId)
    {
        return $query->where('destinataire_id', $userId);
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type_notification', $type);
    }

    // Méthodes métier
    public function marquerCommeLue(): void
    {
        $this->update([
            'est_lue' => true,
            'date_lecture' => now()
        ]);
    }

    public function envoyer(): bool
    {
        // Logique d'envoi selon le canal
        switch ($this->canal_envoi) {
            case 'email':
                return $this->envoyerEmail();
            case 'sms':
                return $this->envoyerSms();
            case 'application':
                return true; // Déjà dans la base
            case 'tous':
                return $this->envoyerEmail() && $this->envoyerSms();
            default:
                return false;
        }
    }

    private function envoyerEmail(): bool
    {
        try {
            // Logique d'envoi d'email
            $this->update([
                'est_envoyee' => true,
                'date_envoi' => now()
            ]);
            return true;
        } catch (\Exception $e) {
            $this->update([
                'erreur_envoi' => $e->getMessage(),
                'tentatives_envoi' => $this->tentatives_envoi + 1
            ]);
            return false;
        }
    }

    private function envoyerSms(): bool
    {
        try {
            // Logique d'envoi SMS
            $this->update([
                'est_envoyee' => true,
                'date_envoi' => now()
            ]);
            return true;
        } catch (\Exception $e) {
            $this->update([
                'erreur_envoi' => $e->getMessage(),
                'tentatives_envoi' => $this->tentatives_envoi + 1
            ]);
            return false;
        }
    }
}
