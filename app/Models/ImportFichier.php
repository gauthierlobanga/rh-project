<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ImportFichier extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'fichiers_import';

    protected $fillable = [
        'nom_fichier',
        'chemin_fichier',
        'type_import',
        'statut_import',
        'nombre_lignes',
        'lignes_traitees',
        'lignes_erreur',
        'resultat_import',
        'erreurs_detaillees',
        'importe_par',
        'date_debut_import',
        'date_fin_import',
        'notes',
    ];

    protected $casts = [
        'resultat_import' => 'array',
        'erreurs_detaillees' => 'array',
        'date_debut_import' => 'datetime',
        'date_fin_import' => 'datetime',
    ];

    // Relations
    public function importateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'importe_par');
    }

    // Media Collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('fichiers_import')
            ->acceptsMimeTypes([
                'text/csv',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->singleFile();
    }

    // Accesseurs
    public function getTauxReussiteAttribute(): float
    {
        if ($this->nombre_lignes === 0) {
            return 0;
        }

        return ($this->lignes_traitees / $this->nombre_lignes) * 100;
    }

    // Scopes
    public function scopeTermines($query)
    {
        return $query->where('statut_import', 'termine');
    }

    public function scopeEnErreur($query)
    {
        return $query->where('statut_import', 'erreur');
    }

    public function scopeParType($query, $type)
    {
        return $query->where('type_import', $type);
    }

    // Méthodes métier
    public function demarrerImport(): void
    {
        $this->update([
            'statut_import' => 'en_cours',
            'date_debut_import' => now(),
        ]);
    }

    public function terminerImport(array $resultat): void
    {
        $this->update([
            'statut_import' => 'termine',
            'date_fin_import' => now(),
            'resultat_import' => $resultat,
            'lignes_traitees' => $resultat['lignes_traitees'] ?? 0,
            'lignes_erreur' => $resultat['lignes_erreur'] ?? 0,
        ]);
    }

    public function marquerErreur(array $erreurs): void
    {
        $this->update([
            'statut_import' => 'erreur',
            'date_fin_import' => now(),
            'erreurs_detaillees' => $erreurs,
        ]);
    }

    public function getResumeImport(): array
    {
        return [
            'fichier' => $this->nom_fichier,
            'type' => $this->type_import,
            'statut' => $this->statut_import,
            'total_lignes' => $this->nombre_lignes,
            'reussite' => $this->lignes_traitees,
            'echec' => $this->lignes_erreur,
            'taux_reussite' => $this->taux_reussite,
            'duree' => $this->date_debut_import && $this->date_fin_import
                ? $this->date_debut_import->diffInSeconds($this->date_fin_import).'s'
                : 'En cours',
            'importateur' => $this->importateur->name,
        ];
    }
}
