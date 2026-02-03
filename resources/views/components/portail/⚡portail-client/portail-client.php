<?php

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\ContratAssuranceVie;
use App\Models\Cotisation;
use App\Models\Sinistre;
use App\Models\NotificationAssurance;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

new class extends Component
{
    use WithPagination, WithFileUploads;

    public $ongletActif = 'contrats';
    public $contratSelectionne = null;
    public $montantPaiement;
    public $modePaiement = 'carte';
    public $afficherModalPaiement = false;
    public $afficherTeleversement = false;
    public $documentTeleverse;
    public $collectionDocument = 'pieces_identite';
    public $descriptionDocument = '';

    protected $queryString = ['ongletActif'];

    public function mount()
    {
        $this->contratSelectionne = Auth::user()->client->contrats()->first();
    }
    #[Computed]
    public function contrats()
    {
        $client = Auth::user()->client;

        return $client->contrats()
            ->with(['produit', 'beneficiaires', 'cotisations' => function ($q) {
                $q->where('statut_paiement', '!=', 'paye')
                    ->where('date_echeance', '<=', now()->addDays(30));
            }])
            ->paginate(5);
    }

    #[Computed]
    public function cotisationsEnAttente()
    {
        $client = Auth::user()->client;

        return $client->cotisations()
            ->where('statut_paiement', '!=', 'paye')
            ->where('date_echeance', '<=', now()->addDays(30))
            ->with('contrat')
            ->get();
    }

    #[Computed]
    public function sinistres()
    {
        $client = Auth::user()->client;

        return  $client->sinistres()
            ->with('contrat')
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function notifications()
    {

        return  NotificationAssurance::where('destinataire_id', Auth::id())
            ->where('est_lue', false)
            ->latest()
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function documents()
    {
        $client = Auth::user()->client;

        return $client->getMedia('*')->paginate(10);
    }

    public function selectionnerContrat($contratId)
    {
        $this->contratSelectionne = ContratAssuranceVie::find($contratId);
        $this->ongletActif = 'details';
    }

    public function initierPaiement($cotisationId)
    {
        $cotisation = Cotisation::find($cotisationId);

        if ($cotisation && $cotisation->contrat->souscripteur_id === Auth::user()->client->id) {
            $this->montantPaiement = $cotisation->montant_restant;
            $this->afficherModalPaiement = true;
        }
    }

    public function traiterPaiement()
    {
        $this->validate([
            'montantPaiement' => 'required|numeric|min:1',
            'modePaiement' => 'required|in:carte,virement,prelevement',
        ]);

        // Intégration avec la passerelle de paiement
        // Stripe, PayPal, etc.

        $this->afficherModalPaiement = false;
        $this->dispatch('paiement-effectue');
    }

    public function telechargerDocument($mediaId)
    {
        $media = Media::find($mediaId);

        if ($media && $media->model_id === Auth::user()->client->id) {
            return response()->download($media->getPath());
        }

        abort(403);
    }

    public function televerserDocument()
    {
        $this->validate([
            'documentTeleverse' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png',
            'collectionDocument' => 'required|in:pieces_identite,justificatifs_domicile,documents_bancaires,autres_documents',
            'descriptionDocument' => 'nullable|string|max:255',
        ]);

        $client = Auth::user()->client;

        $client->addMedia($this->documentTeleverse)
            ->withCustomProperties([
                'description' => $this->descriptionDocument,
                'televerse_par' => Auth::id(),
                'televerse_le' => now()->toDateTimeString(),
            ])
            ->toMediaCollection($this->collectionDocument);

        $this->afficherTeleversement = false;
        $this->documentTeleverse = null;
        $this->descriptionDocument = '';

        $this->dispatch('document-televerse');
    }

    public function declarerSinistre()
    {
        return redirect()->route('client.sinistres.declarer');
    }

    public function marquerNotificationLue($notificationId)
    {
        $notification = NotificationAssurance::find($notificationId);

        if ($notification && $notification->destinataire_id === Auth::id()) {
            $notification->marquerCommeLue();
        }
    }
};
