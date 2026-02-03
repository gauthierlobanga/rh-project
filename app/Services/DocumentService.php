<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ContratAssuranceVie;
use App\Models\Sinistre;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DocumentService
{
    /**
     * Téléverser un document pour un client
     */
    public function uploadClientDocument(Client $client, UploadedFile $file, string $collection, array $metadata = []): Media
    {
        $media = $client->addMedia($file)
            ->withCustomProperties($metadata)
            ->toMediaCollection($collection);

        // Enregistrer dans l'historique
        $this->logDocumentUpload($client, $media, $metadata);

        return $media;
    }

    /**
     * Téléverser un document de contrat
     */
    public function uploadContratDocument(ContratAssuranceVie $contrat, UploadedFile $file, string $collection, array $metadata = []): Media
    {
        $media = $contrat->addMedia($file)
            ->withCustomProperties(array_merge($metadata, [
                'contrat_id' => $contrat->id,
                'souscripteur' => $contrat->souscripteur->utilisateur->name,
            ]))
            ->toMediaCollection($collection);

        return $media;
    }

    /**
     * Téléverser un document de sinistre
     */
    public function uploadSinistreDocument(Sinistre $sinistre, UploadedFile $file, string $collection, array $metadata = []): Media
    {
        $media = $sinistre->addMedia($file)
            ->withCustomProperties(array_merge($metadata, [
                'sinistre_id' => $sinistre->id,
                'type_sinistre' => $sinistre->type_sinistre,
            ]))
            ->toMediaCollection($collection);

        // Mettre à jour la liste des documents reçus
        $documentsRecus = $sinistre->documents_recus ?? [];
        $documentsRecus[] = [
            'nom' => $file->getClientOriginalName(),
            'date' => now()->toDateString(),
            'media_id' => $media->id,
        ];

        $sinistre->update(['documents_recus' => $documentsRecus]);

        return $media;
    }

    /**
     * Générer un lien de téléchargement sécurisé
     */
    public function generateDownloadLink(Media $media, int $expiresInHours = 24): string
    {
        return $media->getTemporaryUrl(now()->addHours($expiresInHours));
    }

    /**
     * Vérifier les documents requis pour un sinistre
     */
    public function checkRequiredDocuments(Sinistre $sinistre): array
    {
        $required = $sinistre->documents_requis ?? [];
        $received = collect($sinistre->documents_recus ?? [])->pluck('nom')->toArray();

        $missing = array_diff($required, $received);
        $completed = array_intersect($required, $received);

        return [
            'required' => $required,
            'received' => $received,
            'missing' => $missing,
            'completed' => $completed,
            'percentage' => count($required) > 0 ? (count($completed) / count($required)) * 100 : 0,
        ];
    }

    /**
     * Générer un PDF de contrat
     */
    public function generateContratPdf(ContratAssuranceVie $contrat): string
    {
        $data = [
            'contrat' => $contrat,
            'souscripteur' => $contrat->souscripteur,
            'beneficiaires' => $contrat->beneficiaires,
            'produit' => $contrat->produit,
            'date_generation' => now(),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('contrats.pdf', $data);

        return $pdf->output();
    }

    /**
     * Archiver les anciens documents
     */
    public function archiveOldDocuments(int $months = 36): int
    {
        $cutoffDate = now()->subMonths($months);

        $archived = Media::where('created_at', '<', $cutoffDate)
            ->where('collection_name', '!=', 'archives')
            ->get();

        foreach ($archived as $media) {
            $media->move($media->model, 'archives');
        }

        return $archived->count();
    }

    private function logDocumentUpload($model, Media $media, array $metadata): void
    {
        // Enregistrer dans l'historique du modèle
        if (method_exists($model, 'historique')) {
            $model->historique()->create([
                'type_evenement' => 'document_upload',
                'description_evenement' => "Document {$media->file_name} téléversé dans la collection {$media->collection_name}",
                'donnees_apres' => [
                    'media_id' => $media->id,
                    'file_name' => $media->file_name,
                    'size' => $media->size,
                    'mime_type' => $media->mime_type,
                    'metadata' => $metadata,
                ],
                'utilisateur_id' => Auth::user()->id,
            ]);
        }
    }
}
