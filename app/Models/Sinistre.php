<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sinistre extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'sinistres';

    protected $fillable = [
        'numero_sinistre',
        'contrat_id',
        'type_sinistre',
        'date_survenance',
        'date_declaration',
        'date_notification',
        'description_sinistre',
        'documents_requis',
        'documents_recus',
        'montant_reclame',
        'montant_accordee',
        'montant_indemnise',
        'statut_sinistre',
        'expert_id',
        'notes_expert',
        'motif_refus',
        'date_traitement',
        'date_indemnisation',
        'beneficiaires_indemnisation',
        'numero_virement',
        'commentaires_internes',
        'est_fraude_suspectee',
        'notes_fraude'
    ];

    protected $casts = [
        'date_survenance' => 'date',
        'date_declaration' => 'date',
        'date_notification' => 'date',
        'date_traitement' => 'date',
        'date_indemnisation' => 'date',
        'documents_requis' => 'array',
        'documents_recus' => 'array',
        'beneficiaires_indemnisation' => 'array',
        'montant_reclame' => 'decimal:2',
        'montant_accordee' => 'decimal:2',
        'montant_indemnise' => 'decimal:2',
        'est_fraude_suspectee' => 'boolean'
    ];

    // Relations
    public function contrat(): BelongsTo
    {
        return $this->belongsTo(ContratAssuranceVie::class, 'contrat_id');
    }

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'expert_id');
    }

    public function paiement(): HasOne
    {
        return $this->hasOne(Paiement::class, 'sinistre_id');
    }

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents_sinistre')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png'])
            ->withResponsiveImages();

        $this->addMediaCollection('rapports_expertise')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png']);

        $this->addMediaCollection('preuves')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'video/mp4']);
    }


    // Scopes
    public function scopeEnCours($query)
    {
        return $query->whereIn('statut_sinistre', ['declare', 'en_cours_examen', 'documents_manquants', 'expertise_en_cours']);
    }

    public function scopeAcceptes($query)
    {
        return $query->whereIn('statut_sinistre', ['accepte', 'indemnise', 'cloture']);
    }

    public function scopeRefuses($query)
    {
        return $query->where('statut_sinistre', 'refuse');
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type_sinistre', $type);
    }

    public function scopeParContrat($query, $contratId)
    {
        return $query->where('contrat_id', $contratId);
    }

    public function scopeParExpert($query, $expertId)
    {
        return $query->where('expert_id', $expertId);
    }

    public function scopeAvecFraudeSuspectee($query)
    {
        return $query->where('est_fraude_suspectee', true);
    }

    // Méthodes métier
    public function assignerExpert(Agent $expert): bool
    {
        $this->update([
            'expert_id' => $expert->id,
            'statut_sinistre' => 'en_cours_examen'
        ]);

        return true;
    }

    public function demanderDocuments(array $documentsRequis): bool
    {
        $this->update([
            'statut_sinistre' => 'documents_manquants',
            'documents_requis' => array_merge($this->documents_requis ?? [], $documentsRequis)
        ]);

        return true;
    }

    public function accepter(float $montantAccorde, array $beneficiaires): bool
    {
        $this->update([
            'statut_sinistre' => 'accepte',
            'montant_accordee' => $montantAccorde,
            'beneficiaires_indemnisation' => $beneficiaires,
            'date_traitement' => now()
        ]);

        return true;
    }

    public function refuser(string $motif): bool
    {
        $this->update([
            'statut_sinistre' => 'refuse',
            'motif_refus' => $motif,
            'date_traitement' => now()
        ]);

        return true;
    }

    public function indemniser(string $numeroVirement): bool
    {
        $this->update([
            'statut_sinistre' => 'indemnise',
            'montant_indemnise' => $this->montant_accordee,
            'numero_virement' => $numeroVirement,
            'date_indemnisation' => now()
        ]);

        // Enregistrer le paiement
        Paiement::create([
            'sinistre_id' => $this->id,
            'type_paiement' => 'indemnisation',
            'montant_paiement' => $this->montant_accordee,
            'date_paiement' => now(),
            'mode_paiement' => 'virement',
            'numero_virement' => $numeroVirement,
            'statut_paiement' => 'valide',
            'valide_par' => Auth::user()->id
        ]);

        return true;
    }

    public function cloturer(): bool
    {
        $this->update(['statut_sinistre' => 'cloture']);
        return true;
    }

    public function verifierDocumentsRecus(): bool
    {
        $requis = $this->documents_requis ?? [];
        $recus = $this->documents_recus ?? [];

        foreach ($requis as $document) {
            if (!in_array($document, $recus)) {
                return false;
            }
        }

        return true;
    }
}
