<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Forms\EmpruntForm;
use App\Models\Emprunt;
use Flux\Flux;

new class extends Component {
    public EmpruntForm $form;

    public $previewData = null;
    public $showPreview = false;
    public $userInfo;
    public $peutEmprunter = true;
    public $raisonBlocage = '';
    public $empruntActif = null;

    public function mount()
    {
        $this->userInfo = Auth::user();
        $this->form->date_debut = now()->format('Y-m-d');

        // Vérifier si l'utilisateur peut emprunter
        $this->verifierPossibiliteEmprunt();
    }

    public function verifierPossibiliteEmprunt()
    {
        $this->peutEmprunter = Emprunt::utilisateurPeutEmprunter(Auth::id());

        if (!$this->peutEmprunter) {
            $this->empruntActif = Emprunt::getEmpruntActif(Auth::id());

            if ($this->empruntActif) {
                $this->raisonBlocage = match ($this->empruntActif->status) {
                    'en_attente' => 'Vous avez déjà une demande d\'emprunt en attente de traitement.',
                    'approuve' => 'Vous avez un emprunt approuvé en attente de signature.',
                    'en_cours' => 'Vous avez déjà un emprunt en cours de remboursement.',
                    default => 'Vous ne pouvez pas faire une nouvelle demande d\'emprunt.',
                };
            }
        }
    }

    #[On('amortissement-create')]
    public function create()
    {
        if (!Auth::check()) {
            session()->flash('error', 'Vous devez être connecté pour créer un emprunt.');
            return;

            // Vérifier à nouveau
            $this->verifierPossibiliteEmprunt();

            if (!$this->peutEmprunter) {
                session()->flash('error', $this->raisonBlocage);
                return;
            }

            $this->reset('previewData', 'showPreview');
            $this->form->reset();
            $this->form->date_debut = now()->format('Y-m-d');
            Flux::modal('amortissement-create')->show();
        }
    }

    public function previewCalcul()
    {
        // Valider les données avant le calcul
        $this->validate();

        // Calculs pour l'aperçu (sans taux - juste les durées)
        $dateDebut = \Carbon\Carbon::parse($this->form->date_debut);
        $dateFin = \Carbon\Carbon::parse($this->form->date_fin_remboursement);
        $dureeMois = $dateDebut->diffInMonths($dateFin);
        $dureeJours = $dateDebut->diffInDays($dateFin);

        // Formater la durée
        $dureeFormatee = $this->formaterDuree($dureeJours);

        $this->previewData = [
            'duree_mois' => $dureeMois,
            'duree_jours' => $dureeJours,
            'duree_formatee' => $dureeFormatee,
            'montant_emprunt' => number_format($this->form->montant_emprunt, 0, ',', ' ') . ' USD',
            'type_amortissement' => $this->form->type_amortissement === 'constant' ? 'Constant' : 'Décroissant',
            'frequence_paiement' => ucfirst($this->form->frequence_paiement),
        ];

        $this->showPreview = true;
    }

    private function formaterDuree($jours): string
    {
        $annees = floor($jours / 365);
        $mois = floor(($jours % 365) / 30);
        $joursRestants = $jours % 30;

        $parts = [];

        if ($annees > 0) {
            $parts[] = $annees . ' an' . ($annees > 1 ? 's' : '');
        }

        if ($mois > 0) {
            $parts[] = $mois . ' mois';
        }

        if ($joursRestants > 0) {
            $parts[] = $joursRestants . ' jour' . ($joursRestants > 1 ? 's' : '');
        }

        return implode(', ', $parts) ?: '0 jour';
    }

    public function save()
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Vous devez être connecté pour créer un emprunt.');
                return;
            }

            // Vérifier à nouveau avant de sauvegarder
            $this->verifierPossibiliteEmprunt();

            if (!$this->peutEmprunter) {
                session()->flash('error', $this->raisonBlocage);
                return;
            }

            $emprunt = $this->form->store();

            session()->flash('success', 'Votre demande d\'emprunt a été soumise avec succès ! Notre équipe l\'étudiera et vous proposera un taux d\'intérêt.');

            Flux::modal('amortissement-create')->close();

            $this->dispatch('emprunt-created');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la création de l\'emprunt: ' . $e->getMessage());
        }
    }

    public function rules()
    {
        return [
            'form.montant_emprunt' => ['required', 'numeric', 'min:1000', 'max:1000000'],
            'form.date_fin_remboursement' => ['required', 'date', 'after:form.date_debut'],
            'form.type_amortissement' => ['required', 'in:constant,decroissant'],
            'form.frequence_paiement' => ['required', 'in:mensuel,trimestriel,annuel'],
            'form.date_debut' => ['required', 'date', 'after_or_equal:today'],
            'form.notes' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages()
    {
        return [
            'form.montant_emprunt.required' => 'Le montant de l\'emprunt est requis.',
            'form.montant_emprunt.min' => 'Le montant minimum est de 1000USD.',
            'form.montant_emprunt.max' => 'Le montant maximum est de 1,000,000USD.',
            'form.date_fin_remboursement.required' => 'La date de fin de remboursement est requise.',
            'form.date_fin_remboursement.after' => 'La date de fin doit être après la date de début.',
            'form.date_debut.required' => 'La date de début est requise.',
            'form.date_debut.after_or_equal' => 'La date de début ne peut pas être dans le passé.',
            'form.notes.required' => 'Veuillez indiquer le motif de l\'emprunt.',
        ];
    }
};
?>

<flux:modal name="amortissement-create" flyout size="lg">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Demande d'emprunt</flux:heading>
            <flux:text class="mt-2">
                @if ($userInfo)
                    Demande pour : <strong>{{ $userInfo->name }}</strong>
                @endif
            </flux:text>
        </div>

        @if (!$peutEmprunter && $empruntActif)
            <div
                class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <div class="flex items-center">
                    <flux:icon.exclamation-triangle class="w-5 h-5 mr-2 text-yellow-600 dark:text-yellow-400" />
                    <div>
                        <flux:text class="font-medium text-yellow-800 dark:text-yellow-300">
                            Emprunt existant
                        </flux:text>
                        <flux:text class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                            {{ $raisonBlocage }}
                        </flux:text>
                        <flux:text class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                            Référence : EMP-{{ str_pad($empruntActif->id, 6, '0', STR_PAD_LEFT) }} |
                            Statut :
                            {{ $empruntActif->status === 'en_attente' ? 'En attente' : ($empruntActif->status === 'approuve' ? 'Approuvé' : 'En cours') }}
                        </flux:text>
                    </div>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="previewCalcul" class="space-y-4" @if (!$peutEmprunter) disabled @endif>
            <!-- Montant de l'emprunt -->
            <flux:input :label="__('Montant souhaité (USD)')" type="number" step="100"
                wire:model="form.montant_emprunt" placeholder="Ex: 10000" required :disabled="!$peutEmprunter" />
            @error('form.montant_emprunt')
                <flux:text color="danger" class="mt-1">{{ $message }}</flux:text>
            @enderror

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Date de début souhaitée -->
                <flux:input :label="__('Date de début souhaitée')" type="date" wire:model="form.date_debut" required
                    :disabled="!$peutEmprunter" />
                @error('form.date_debut')
                    <flux:text color="danger" class="mt-1">{{ $message }}</flux:text>
                @enderror

                <!-- Date de fin de remboursement -->
                <flux:input :label="__('Date de fin de remboursement')" type="date"
                    wire:model="form.date_fin_remboursement" required :disabled="!$peutEmprunter" />
                @error('form.date_fin_remboursement')
                    <flux:text color="danger" class="mt-1">{{ $message }}</flux:text>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Type d'amortissement -->
                <flux:select :label="__('Type d\'amortissement')" wire:model="form.type_amortissement" required
                    :disabled="!$peutEmprunter">
                    <option value="constant">Mensualités constantes</option>
                    <option value="decroissant">Mensualités décroissantes</option>
                </flux:select>
                @error('form.type_amortissement')
                    <flux:text color="danger" class="mt-1">{{ $message }}</flux:text>
                @enderror

                <!-- Fréquence de paiement -->
                <flux:select :label="__('Fréquence de paiement')" wire:model="form.frequence_paiement" required
                    :disabled="!$peutEmprunter">
                    <option value="mensuel">Mensuel</option>
                    <option value="trimestriel">Trimestriel</option>
                    <option value="annuel">Annuel</option>
                </flux:select>
                @error('form.frequence_paiement')
                    <flux:text color="danger" class="mt-1">{{ $message }}</flux:text>
                @enderror
            </div>

            <!-- Notes (motif obligatoire) -->
            <flux:textarea :label="__('Motif de l\'emprunt *')" wire:model="form.notes"
                placeholder="Précisez l'utilisation prévue des fonds (achat immobilier, voiture, projet professionnel, etc.)..."
                rows="3" required :disabled="!$peutEmprunter" />
            @error('form.notes')
                <flux:text color="danger" class="mt-1">{{ $message }}</flux:text>
            @enderror

            <!-- Aperçu -->
            @if ($showPreview && $previewData)
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <flux:heading size="md" class="mb-3">Récapitulatif de votre demande</flux:heading>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <flux:text class="text-gray-600 dark:text-gray-400">Montant</flux:text>
                            <flux:text class="font-semibold">{{ $previewData['montant_emprunt'] }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text class="text-gray-600 dark:text-gray-400">Durée</flux:text>
                            <flux:text class="font-semibold">{{ $previewData['duree_formatee'] }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text class="text-gray-600 dark:text-gray-400">Type d'amortissement</flux:text>
                            <flux:text class="font-semibold">{{ $previewData['type_amortissement'] }}</flux:text>
                        </div>
                        <div class="flex justify-between">
                            <flux:text class="text-gray-600 dark:text-gray-400">Fréquence</flux:text>
                            <flux:text class="font-semibold">{{ $previewData['frequence_paiement'] }}</flux:text>
                        </div>
                    </div>

                    <flux:text size="sm" class="text-gray-500 mt-3">
                        ⚠️ Les conditions finales (taux d'intérêt, mensualité, frais) seront déterminées
                        par notre équipe après étude de votre dossier et vous seront communiquées.
                    </flux:text>
                </div>
            @endif

            <flux:spacer />

            <div class="flex items-center justify-between">
                <flux:button type="button" wire:click="previewCalcul" variant="primary"
                    :disabled="!$this->form->montant_emprunt || !$this->form->date_debut || !$this->form->date_fin_remboursement || !$peutEmprunter">
                    <flux:icon.eye class="w-4 h-4 mr-2" />
                    Prévisualiser
                </flux:button>

                <div class="flex gap-2">
                    <flux:button type="button" variant="ghost"
                        wire:click="$dispatch('close-modal', { id: 'amortissement-create' })">
                        Annuler
                    </flux:button>

                    <flux:button type="button" wire:click="save" variant="primary"
                        class="bg-accent text-white cursor-pointer" :disabled="!$showPreview || !$peutEmprunter">
                        <flux:icon.paper-airplane class="w-4 h-4 mr-2" />
                        Soumettre la demande
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</flux:modal>
