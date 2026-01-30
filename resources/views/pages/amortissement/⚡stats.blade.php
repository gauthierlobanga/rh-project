<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Forms\EmpruntForm;
use Flux\Flux;

new class extends Component {
    public EmpruntForm $form;

    public $previewData = null;
    public $showPreview = false;
    public $userInfo;
    public $showFrais = false;

    // Paramètres de frais par défaut (pourrait venir de la base de données)
    public $fraisParDefaut = [
        'dossier_pourcentage' => 1, // 1% du montant
        'dossier_minimum' => 500,
        'dossier_maximum' => 2000,
        'assurance_taux' => 0.3, // 0.3% du capital restant annuel
        'notaire_pourcentage' => 2, // 2% pour achat immobilier
    ];

    public function mount()
    {
        $this->userInfo = Auth::user();
        $this->form->date_debut = now()->format('Y-m-d');
    }

    #[On('amortissement-create')]
    public function create()
    {
        if (!Auth::check()) {
            session()->flash('error', 'Vous devez être connecté pour créer un emprunt.');
            return;
        }

        $this->reset('previewData', 'showPreview');
        $this->form->reset();
        $this->form->date_debut = now()->format('Y-m-d');

        // Estimer les frais basés sur le montant par défaut
        $this->estimerFrais();

        Flux::modal('amortissement-create')->show();
    }

    public function estimerFrais()
    {
        // Estimation automatique des frais basée sur le montant
        if ($this->form->montant_emprunt > 0) {
            // Frais de dossier (1% avec min 500USD et max 2000USD)
            $fraisDossier = $this->form->montant_emprunt * ($this->fraisParDefaut['dossier_pourcentage'] / 100);
            $fraisDossier = max($this->fraisParDefaut['dossier_minimum'], min($fraisDossier, $this->fraisParDefaut['dossier_maximum']));
            $this->form->frais_dossier_estime = round($fraisDossier, 2);

            // Frais d'assurance (0.3% du capital annuel)
            $fraisAssurance = $this->form->montant_emprunt * ($this->fraisParDefaut['assurance_taux'] / 100);
            $this->form->frais_assurance_estime = round($fraisAssurance, 2);

            // Frais de notaire (seulement pour achat immobilier)
            $this->form->frais_notaire_estime = 0;

            // Autres frais
            $this->form->frais_autres_estime = 0;
        }
    }

    public function updated($property)
    {
        // Lorsque le montant change, ré-estimer les frais
        if ($property === 'form.montant_emprunt') {
            $this->estimerFrais();
        }
    }

    public function previewCalcul()
    {
        $this->validate();

        // Calculs pour l'aperçu
        $dateDebut = \Carbon\Carbon::parse($this->form->date_debut);
        $dateFin = \Carbon\Carbon::parse($this->form->date_fin_remboursement);
        $dureeMois = $dateDebut->diffInMonths($dateFin);

        // Taux périodique
        $tauxPeriodique = $this->form->taux_interet_annuel / 100;

        switch ($this->form->frequence_paiement) {
            case 'mensuel':
                $tauxPeriodique = $tauxPeriodique / 12;
                $periodes = $dureeMois;
                break;
            case 'trimestriel':
                $tauxPeriodique = $tauxPeriodique / 4;
                $periodes = $dureeMois / 3;
                break;
            case 'annuel':
                $periodes = $dureeMois / 12;
                break;
        }

        // Calculs
        $montant = $this->form->montant_emprunt;

        if ($this->form->type_amortissement === 'constant') {
            $mensualite = $montant * ($tauxPeriodique / (1 - pow(1 + $tauxPeriodique, -$periodes)));
        } else {
            $amortissementCapital = $montant / $periodes;
            $mensualite = $amortissementCapital + $montant * $tauxPeriodique;
        }

        $totalInterets = $mensualite * $periodes - $montant;
        $totalARembourser = $montant + $totalInterets;

        // Calcul des frais totaux
        $totalFrais = $this->form->frais_dossier_estime + $this->form->frais_assurance_estime + $this->form->frais_notaire_estime + $this->form->frais_autres_estime;

        $montantTotalDu = $totalARembourser + $totalFrais;

        // Calcul du TAEG estimé
        $taegEstime = $this->form->calculerTAEGEstime($montant, $mensualite, $dureeMois, $totalFrais);

        $this->previewData = [
            'duree_mois' => $dureeMois,
            'montant_mensualite' => number_format($mensualite, 2, ',', ' '),
            'total_interets' => number_format($totalInterets, 2, ',', ' '),
            'total_a_rembourser' => number_format($totalARembourser, 2, ',', ' '),
            'total_frais' => number_format($totalFrais, 2, ',', ' '),
            'montant_total_du' => number_format($montantTotalDu, 2, ',', ' '),
            'cout_total' => number_format(($totalInterets / $montant) * 100, 2) . '%',
            'taeg_estime' => number_format($taegEstime, 2) . '%',
        ];

        $this->showPreview = true;
    }

    public function save()
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Vous devez être connecté pour créer un emprunt.');
                return;
            }

            $emprunt = $this->form->store();

            session()->flash('success', 'Votre demande d\'emprunt a été soumise avec succès ! Elle sera examinée par notre équipe.');

            Flux::modal('amortissement-create')->hide();

            $this->dispatch('emprunt-created');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la création de l\'emprunt: ' . $e->getMessage());
        }
    }

    public function rules()
    {
        return $this->form->rules();
    }
};
?>
<?php

use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Forms\EmpruntForm;
use Flux\Flux;

new class extends Component {
    public EmpruntForm $form;

    public $previewData = null;
    public $showPreview = false;
    public $userInfo;
    public $showFrais = false;

    #[Validate]
    public $montant_emprunt;
    #[Validate]
    public $date_fin_remboursement;
    public $taux_interet_annuel = 5.5;
    public $type_amortissement = 'constant';
    public $frequence_paiement = 'mensuel';
    #[Validate]
    public $date_debut;
    public $notes = '';
    public $frais_dossier_estime = 0;
    public $frais_assurance_estime = 0;
    public $frais_notaire_estime = 0;
    public $frais_autres_estime = 0;

    public $fraisParDefaut = [
        'dossier_pourcentage' => 1,
        'dossier_minimum' => 500,
        'dossier_maximum' => 2000,
        'assurance_taux' => 0.3,
        'notaire_pourcentage' => 2,
    ];

    public function mount()
    {
        $this->userInfo = Auth::user();
        $this->date_debut = now()->format('Y-m-d');
    }

    #[On('amortissement-create')]
    public function create()
    {
        if (!Auth::check()) {
            session()->flash('error', 'Vous devez être connecté pour créer un emprunt.');
            return;
        }

        $this->reset('previewData', 'showPreview');
        $this->resetForm();
        Flux::modal('amortissement-create')->show();
    }

    public function resetForm()
    {
        $this->montant_emprunt = null;
        $this->date_fin_remboursement = null;
        $this->taux_interet_annuel = 5.5;
        $this->type_amortissement = 'constant';
        $this->frequence_paiement = 'mensuel';
        $this->date_debut = now()->format('Y-m-d');
        $this->notes = '';
        $this->frais_dossier_estime = 0;
        $this->frais_assurance_estime = 0;
        $this->frais_notaire_estime = 0;
        $this->frais_autres_estime = 0;

        $this->estimerFrais();
    }

    public function estimerFrais()
    {
        if ($this->montant_emprunt > 0) {
            $fraisDossier = $this->montant_emprunt * ($this->fraisParDefaut['dossier_pourcentage'] / 100);
            $fraisDossier = max($this->fraisParDefaut['dossier_minimum'], min($fraisDossier, $this->fraisParDefaut['dossier_maximum']));
            $this->frais_dossier_estime = round($fraisDossier, 2);

            $fraisAssurance = $this->montant_emprunt * ($this->fraisParDefaut['assurance_taux'] / 100);
            $this->frais_assurance_estime = round($fraisAssurance, 2);

            $this->frais_notaire_estime = 0;
            $this->frais_autres_estime = 0;
        }
    }

    public function updated($property)
    {
        if ($property === 'montant_emprunt') {
            $this->estimerFrais();
        }
    }

    public function previewCalcul()
    {
        // Valider les données avant le calcul
        $this->validate();

        // Mettre à jour le formulaire avec les valeurs
        $this->form->montant_emprunt = $this->montant_emprunt;
        $this->form->date_fin_remboursement = $this->date_fin_remboursement;
        $this->form->taux_interet_annuel = $this->taux_interet_annuel;
        $this->form->type_amortissement = $this->type_amortissement;
        $this->form->frequence_paiement = $this->frequence_paiement;
        $this->form->date_debut = $this->date_debut;
        $this->form->notes = $this->notes;
        $this->form->frais_dossier_estime = $this->frais_dossier_estime;
        $this->form->frais_assurance_estime = $this->frais_assurance_estime;
        $this->form->frais_notaire_estime = $this->frais_notaire_estime;
        $this->form->frais_autres_estime = $this->frais_autres_estime;

        // Calculs pour l'aperçu
        $dateDebut = \Carbon\Carbon::parse($this->date_debut);
        $dateFin = \Carbon\Carbon::parse($this->date_fin_remboursement);
        $dureeMois = $dateDebut->diffInMonths($dateFin);

        // Taux périodique
        $tauxPeriodique = $this->taux_interet_annuel / 100;

        switch ($this->frequence_paiement) {
            case 'mensuel':
                $tauxPeriodique = $tauxPeriodique / 12;
                $periodes = $dureeMois;
                break;
            case 'trimestriel':
                $tauxPeriodique = $tauxPeriodique / 4;
                $periodes = $dureeMois / 3;
                break;
            case 'annuel':
                $periodes = $dureeMois / 12;
                break;
        }

        // Calculs
        $montant = $this->montant_emprunt;

        if ($this->type_amortissement === 'constant') {
            $mensualite = $montant * ($tauxPeriodique / (1 - pow(1 + $tauxPeriodique, -$periodes)));
        } else {
            $amortissementCapital = $montant / $periodes;
            $mensualite = $amortissementCapital + $montant * $tauxPeriodique;
        }

        $totalInterets = $mensualite * $periodes - $montant;
        $totalARembourser = $montant + $totalInterets;

        // Calcul des frais totaux
        $totalFrais = $this->frais_dossier_estime + $this->frais_assurance_estime + $this->frais_notaire_estime + $this->frais_autres_estime;

        $montantTotalDu = $totalARembourser + $totalFrais;

        // Calcul du TAEG estimé
        $taegEstime = $this->form->calculerTAEGEstime($montant, $mensualite, $dureeMois, $totalFrais);

        $this->previewData = [
            'duree_mois' => $dureeMois,
            'montant_mensualite' => number_format($mensualite, 2, ',', ' '),
            'total_interets' => number_format($totalInterets, 2, ',', ' '),
            'total_a_rembourser' => number_format($totalARembourser, 2, ',', ' '),
            'total_frais' => number_format($totalFrais, 2, ',', ' '),
            'montant_total_du' => number_format($montantTotalDu, 2, ',', ' '),
            'cout_total' => number_format(($totalInterets / $montant) * 100, 2) . '%',
            'taeg_estime' => number_format($taegEstime, 2) . '%',
        ];

        $this->showPreview = true;
    }

    public function save()
    {
        try {
            if (!Auth::check()) {
                session()->flash('error', 'Vous devez être connecté pour créer un emprunt.');
                return;
            }

            $this->validate();

            $data = [
                'montant_emprunt' => $this->montant_emprunt,
                'date_fin_remboursement' => $this->date_fin_remboursement,
                'taux_interet_annuel' => $this->taux_interet_annuel,
                'type_amortissement' => $this->type_amortissement,
                'frequence_paiement' => $this->frequence_paiement,
                'date_debut' => $this->date_debut,
                'notes' => $this->notes,
                'frais_dossier_estime' => $this->frais_dossier_estime,
                'frais_assurance_estime' => $this->frais_assurance_estime,
                'frais_notaire_estime' => $this->frais_notaire_estime,
                'frais_autres_estime' => $this->frais_autres_estime,
            ];

            $emprunt = $this->form->store($data);

            Flux::modal('amortissement-create')->close();

            session()->flash('success', 'Votre demande d\'emprunt a été soumise avec succès ! Elle sera examinée par notre équipe.');

            $this->redirect(route('amortissement.list'), navigate: true);

            $this->dispatch('emprunt-created');
        } catch (\Exception $e) {
            session()->flash('error', 'Erreur lors de la création de l\'emprunt: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->dispatch('amortissement-create');
    }

    public function rules()
    {
        return [
            'montant_emprunt' => ['required', 'numeric', 'min:1000', 'max:1000000'],
            'date_fin_remboursement' => ['required', 'date', 'after:date_debut'],
            'taux_interet_annuel' => ['required', 'numeric', 'min:0.1', 'max:20'],
            'type_amortissement' => ['required', 'in:constant,decroissant'],
            'frequence_paiement' => ['required', 'in:mensuel,trimestriel,annuel'],
            'date_debut' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
            'frais_dossier_estime' => ['numeric', 'min:0'],
            'frais_assurance_estime' => ['numeric', 'min:0'],
            'frais_notaire_estime' => ['numeric', 'min:0'],
            'frais_autres_estime' => ['numeric', 'min:0'],
        ];
    }

    public function messages()
    {
        return [
            'montant_emprunt.required' => 'Le montant de l\'emprunt est requis.',
            'montant_emprunt.min' => 'Le montant minimum est de :min USD.',
            'montant_emprunt.max' => 'Le montant maximum est de :max USD.',
            'date_fin_remboursement.required' => 'La date de fin de remboursement est :required',
            'date_fin_remboursement.after' => 'La date de fin doit être après la date de début.',
            'taux_interet_annuel.required' => 'Le taux d\'intérêt est requis.',
            'taux_interet_annuel.min' => 'Le taux d\'intérêt minimum est de 0.1%.',
            'taux_interet_annuel.max' => 'Le taux d\'intérêt maximum est de 20%.',
            'date_debut.required' => 'La date de début est requise.',
            'date_debut.after_or_equal' => 'La date de début ne peut pas être dans le passé.',
        ];
    }
};
?>

{{-- Modal create emprunt --}}
<flux:modal name="amortissement-create" flyout size="xl">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Demande d'emprunt</flux:heading>
            <flux:text class="mt-2">
                @if ($userInfo)
                    Demande pour : <strong>{{ $userInfo->name }}</strong> ({{ $userInfo->email }})
                @endif
            </flux:text>
        </div>

        <form wire:submit.prevent="previewCalcul" class="space-y-4">
            <!-- Montant de l'emprunt -->
            <flux:input :label="__('Montant de l\'emprunt (USD)')" type="number" step="100"
                wire:model="montant_emprunt" placeholder="Ex: 10000" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Date de début -->
                <flux:input :label="__('Date de début')" type="date" wire:model="date_debut" />


                <!-- Date de fin -->
                <flux:input :label="__('Date de fin de remboursement')" type="date"
                    wire:model="date_fin_remboursement" />

            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Taux d'intérêt proposé -->
                <flux:input :label="__('Taux d\'intérêt souhaité (%)')" type="number" step="0.01"
                    wire:model="taux_interet_annuel" placeholder="Ex: 5.5" />


                <!-- Type d'amortissement -->
                <flux:select :label="__('Type d\'amortissement')" wire:model="type_amortissement">
                    <option value="constant">Mensualités constantes</option>
                    <option value="decroissant">Mensualités décroissantes</option>
                </flux:select>

                <!-- Fréquence de paiement -->
                <flux:select :label="__('Fréquence de paiement')" wire:model="frequence_paiement">
                    <option value="mensuel">Mensuel</option>
                    <option value="trimestriel">Trimestriel</option>
                    <option value="annuel">Annuel</option>
                </flux:select>
            </div>

            <!-- Section des frais (dépliable) -->
            <div wire:transition class="border border-gray-200 dark:border-gray-700 rounded-lg">
                <button type="button" wire:click="$toggle('showFrais')"
                    class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <div class="flex items-center">
                        <flux:icon.banknotes class="w-5 h-5 mr-2 text-gray-500" />
                        <span class="font-medium">Frais estimés (optionnel)</span>
                    </div>
                    <flux:icon.chevron-down
                        class="w-4 h-4 transition-transform duration-200 {{ $showFrais ? 'rotate-180' : '' }}" />
                </button>


            </div>

            <!-- Notes -->
            <flux:textarea :label="__('Motif de l\'emprunt (optionnel)')" wire:model="notes"
                placeholder="Précisez l'utilisation prévue des fonds..." rows="3" />

            <!-- Aperçu des calculs -->
            @if ($showPreview && $previewData)
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <flux:heading size="md" class="mb-3">Simulation de votre emprunt</flux:heading>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400">Durée</flux:text>
                            <flux:text class="font-semibold">{{ $previewData['duree_mois'] }} mois</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400">Mensualité estimée</flux:text>
                            <flux:text class="font-semibold">{{ $previewData['montant_mensualite'] }} USD</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400">Total intérêts</flux:text>
                            <flux:text class="font-semibold">{{ $previewData['total_interets'] }} USD</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400">TAEG estimé</flux:text>
                            <flux:text class="font-semibold">{{ $previewData['taeg_estime'] }}</flux:text>
                        </div>
                    </div>

                    <div class="border-t border-blue-200 dark:border-blue-700 pt-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400">Frais totaux</flux:text>
                                <flux:text class="font-semibold">{{ $previewData['total_frais'] }} USD</flux:text>
                            </div>
                            <div>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400">À rembourser</flux:text>
                                <flux:text class="font-semibold">{{ $previewData['total_a_rembourser'] }} USD
                                </flux:text>
                            </div>
                            <div>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400">Total dû</flux:text>
                                <flux:text class="font-semibold">{{ $previewData['montant_total_du'] }} USD</flux:text>
                            </div>
                            <div>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400">Coût total</flux:text>
                                <flux:text class="font-semibold">{{ $previewData['cout_total'] }}</flux:text>
                            </div>
                        </div>
                    </div>

                    <flux:text size="sm" class="text-gray-500 mt-3">
                        Ces chiffres sont une simulation basée sur vos paramètres et les frais estimés.
                        Le taux final (TAEG) et les conditions seront validés par notre équipe après étude de votre
                        dossier.
                    </flux:text>
                </div>
            @endif

            <flux:spacer />

            <div class="flex items-center justify-between">
                <flux:button class="cursor-pointer" type="button" wire:click="previewCalcul" variant="primary" <!--
                    :disabled="!$montant_emprunt || !$date_debut || !$date_fin_remboursement" --}}>
                    Simuler
                </flux:button>

                <div class="flex gap-2">
                    <flux:button variant="ghost" class="cursor-pointer"
                        wire:click="$dispatch('close-modal', { id: 'amortissement-create' })">
                        Annuler
                    </flux:button>

                    <flux:button wire:click="save" variant="primary" class="bg-accent text-white cursor-pointer"
                        {{-- :disabled="!$showPreview" --}}>
                        Soumettre la demande
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</flux:modal>

{{-- Modal create emprunt --}}
<flux:modal name="amortissement-create" flyout size="xl">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">Demande d'emprunt</flux:heading>
            <flux:text class="mt-2">
                @if ($userInfo)
                    Demande pour : <strong>{{ $userInfo->name }}</strong> ({{ $userInfo->email }})
                @endif
            </flux:text>
        </div>

        <form wire:submit.prevent="previewCalcul" class="space-y-4">
            <!-- Montant de l'emprunt -->
            <flux:input :label="__('Montant de l\'emprunt (USD)')" type="number" step="100"
                wire:model="form.montant_emprunt" placeholder="Ex: 10000" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Date de début -->
                <flux:input :label="__('Date de début')" type="date" wire:model="form.date_debut" />

                <!-- Date de fin -->
                <flux:input :label="__('Date de fin de remboursement')" type="date"
                    wire:model="form.date_fin_remboursement" />

            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Taux d'intérêt proposé -->
                <flux:input :label="__('Taux d\'intérêt souhaité (%)')" type="number" step="0.01"
                    wire:model="form.taux_interet_annuel" placeholder="Ex: 5.5" />

                <!-- Type d'amortissement -->
                <flux:select :label="__('Type d\'amortissement')" wire:model="form.type_amortissement">
                    <option value="constant">Mensualités constantes</option>
                    <option value="decroissant">Mensualités décroissantes</option>
                </flux:select>

                <!-- Fréquence de paiement -->
                <flux:select :label="__('Fréquence de paiement')" wire:model="form.frequence_paiement">
                    <option value="mensuel">Mensuel</option>
                    <option value="trimestriel">Trimestriel</option>
                    <option value="annuel">Annuel</option>
                </flux:select>
            </div>

            <!-- Section des frais (dépliable) -->
            <div wire:transition
                class="border border-gray-200 dark:border-gray-700 rounded-lg transition-all duration-300 ease-in-out">
                <button type="button" wire:click="$toggle('showFrais')"
                    class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <div class="flex items-center">
                        <flux:icon.banknotes class="w-5 h-5 mr-2 text-gray-500" />
                        <span class="font-medium">Frais estimés (optionnel)</span>
                    </div>
                    <flux:icon.chevron-down
                        class="w-4 h-4 transition-transform duration-200 {{ $showFrais ? 'rotate-180' : '' }}" />
                </button>

                @if ($showFrais)
                    <div class="px-4 pb-4 space-y-4">
                        <flux:text size="sm" class="text-gray-500">
                            Les frais réels seront validés par notre équipe après étude de votre dossier.
                        </flux:text>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <flux:input :label="__('Frais de dossier estimés (USD)')" type="number" step="0.01"
                                wire:model="form.frais_dossier_estime" placeholder="0" />

                            <flux:input :label="__('Frais d\'assurance estimés (USD)')" type="number" step="0.01"
                                wire:model="form.frais_assurance_estime" placeholder="0" />

                            <flux:input :label="__('Frais de notaire estimés (USD)')" type="number" step="0.01"
                                wire:model="form.frais_notaire_estime" placeholder="0" />

                            <flux:input :label="__('Autres frais estimés (USD)')" type="number" step="0.01"
                                wire:model="form.frais_autres_estime" placeholder="0" />
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg">
                            <div class="flex items-center">
                                <flux:icon.information-circle class="w-5 h-5 mr-2 text-blue-500" />
                                <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                                    Estimation basée sur 1% de frais de dossier (min 500USD, max 2000USD)
                                    et 0.3% d'assurance annuelle. Ces frais sont indicatifs.
                                </flux:text>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Notes -->
            <flux:textarea :label="__('Motif de l\'emprunt (optionnel)')" wire:model="form.notes"
                placeholder="Précisez l'utilisation prévue des fonds..." rows="4" />

            <!-- Aperçu des calculs -->
            @if ($showPreview && $previewData)
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <flux:heading size="md" class="mb-3">Simulation de votre emprunt</flux:heading>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400">Durée</flux:text>
                            <flux:text class="font-semibold">{{ $previewData['duree_mois'] }} mois</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400">Mensualité estimée</flux:text>
                            <flux:text class="font-semibold">{{ $previewData['montant_mensualite'] }} USD</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400">Total intérêts</flux:text>
                            <flux:text class="font-semibold">{{ $previewData['total_interets'] }} USD</flux:text>
                        </div>
                        <div>
                            <flux:text class="text-sm text-gray-600 dark:text-gray-400">TAEG estimé</flux:text>
                            <flux:text class="font-semibold">{{ $previewData['taeg_estime'] }}</flux:text>
                        </div>
                    </div>

                    <div class="border-t border-blue-200 dark:border-blue-700 pt-4">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400">Frais totaux</flux:text>
                                <flux:text class="font-semibold">{{ $previewData['total_frais'] }} USD</flux:text>
                            </div>
                            <div>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400">À rembourser</flux:text>
                                <flux:text class="font-semibold">{{ $previewData['total_a_rembourser'] }} USD
                                </flux:text>
                            </div>
                            <div>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400">Total dû</flux:text>
                                <flux:text class="font-semibold">{{ $previewData['montant_total_du'] }} USD
                                </flux:text>
                            </div>
                            <div>
                                <flux:text class="text-sm text-gray-600 dark:text-gray-400">Coût total</flux:text>
                                <flux:text class="font-semibold">{{ $previewData['cout_total'] }}</flux:text>
                            </div>
                        </div>
                    </div>

                    <flux:text size="sm" class="text-gray-500 mt-3">
                        ⚠️ Ces chiffres sont une simulation basée sur vos paramètres et les frais estimés.
                        Le taux final (TAEG) et les conditions seront validés par notre équipe après étude de votre
                        dossier.
                    </flux:text>
                </div>
            @endif

            <flux:spacer />

            <div class="flex items-center justify-between">
                <flux:button type="button" wire:click="previewCalcul" variant="primary"
                    :disabled="!$this->form->montant_emprunt || !$this->form->date_debut || !$this->form->date_fin_remboursement">
                    <flux:icon.calculator class="w-4 h-4 mr-2" />
                    Simuler
                </flux:button>

                <div class="flex gap-2">
                    <flux:button type="button" variant="ghost"
                        wire:click="$dispatch('close-modal', { id: 'amortissement-create' })">
                        Annuler
                    </flux:button>

                    <flux:button type="button" wire:click="save" variant="primary"
                        class="bg-accent text-white cursor-pointer" :disabled="!$showPreview">
                        <flux:icon.paper-airplane class="w-4 h-4 mr-2" />
                        Soumettre la demande
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
</flux:modal>
