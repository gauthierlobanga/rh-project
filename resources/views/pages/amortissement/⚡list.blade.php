<?php

use App\Models\Emprunt;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmpruntsExport;
use App\mports\EmpruntsImport;
use Barryvdh\DomPDF\Facade\Pdf;

new #[Title('Mes Emprunts')] class extends Component {
    use WithPagination;

    #[Url]
    public $sortBy = 'montant_emprunt';

    public int $perPage = 10;

    public $search = '';

    #[Url]
    public $filterStatus = '';

    #[Url]
    public $filterTypeAmortissement = '';

    public $sortDirection = 'desc';

    public $filters = [
        'date_debut' => null,
        'date_fin' => null,
        'montant_min' => null,
        'montant_max' => null,
        'duree_mois_min' => null,
        'duree_mois_max' => null,
    ];

    // Options pour les filtres
    public $statusOptions = [
        'en_attente' => 'En attente',
        'approuve' => 'Approuvé',
        'rejete' => 'Rejeté',
        'termine' => 'Terminé',
        'defaut' => 'Défaut',
    ];

    public $typeAmortissementOptions = [
        'constant' => 'Amortissement constant',
        'decroissant' => 'Amortissement décroissant',
    ];

    public function mount()
    {
        // Vérifier s'il y a des notifications non lues
        if (Auth::check()) {
            $this->checkNotifications();
        }
    }

    public function checkNotifications()
    {
        $user = Auth::user();
        $notificationsNonLues = $user
            ->unreadNotifications()
            ->whereIn('type', ['App\Notifications\EmpruntApprouve', 'App\Notifications\ArgentDisponible', 'App\Notifications\EcheanceProchaine'])
            ->count();

        // Vous pouvez dispatcher un événement pour mettre à jour le compteur de notifications
        if ($notificationsNonLues > 0) {
            $this->dispatch('notifications-updated', count: $notificationsNonLues);
        }
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'filterStatus', 'filterTypeAmortissement', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function applyFilters()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->filterTypeAmortissement = '';
        $this->filters = [
            'date_debut' => null,
            'date_fin' => null,
            'montant_min' => null,
            'montant_max' => null,
            'duree_mois_min' => null,
            'duree_mois_max' => null,
        ];
        $this->resetPage();
    }

    #[Computed]
    public function emprunts()
    {
        return Emprunt::with(['user', 'echeances', 'paiements'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('id', 'like', '%' . $this->search . '%')
                        ->orWhere('montant_emprunt', 'like', '%' . $this->search . '%')
                        ->orWhere('notes', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterTypeAmortissement, function ($query) {
                $query->where('type_amortissement', $this->filterTypeAmortissement);
            })
            ->when($this->filters['date_debut'], function ($query, $date) {
                $query->where('date_debut', '>=', $date);
            })
            ->when($this->filters['date_fin'], function ($query, $date) {
                $query->where('date_fin_remboursement', '<=', $date);
            })
            ->when($this->filters['montant_min'], function ($query, $montant) {
                $query->where('montant_emprunt', '>=', $montant);
            })
            ->when($this->filters['montant_max'], function ($query, $montant) {
                $query->where('montant_emprunt', '<=', $montant);
            })
            ->when($this->filters['duree_mois_min'], function ($query, $duree) {
                $query->where('duree_mois', '>=', $duree);
            })
            ->when($this->filters['duree_mois_max'], function ($query, $duree) {
                $query->where('duree_mois', '<=', $duree);
            })
            ->where('user_id', Auth::id())
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function annulerEmprunt($empruntId)
    {
        $emprunt = Emprunt::find($empruntId);

        if ($emprunt && Auth::user()->can('update', $emprunt)) {
            if ($emprunt->status !== 'approuve' && $emprunt->status !== 'termine') {
                $emprunt->update(['status' => 'annule']);
                session()->flash('success', 'Emprunt annulé avec succès!');
            }
        }
    }

    // Ajouter une méthode pour voir les détails avec tableau d'amortissement
    public function voirDetails($empruntId)
    {
        $this->dispatch('show-amortissement-table', id: $empruntId);
    }

    // Méthode pour marquer une notification comme lue
    public function marquerNotificationLue($notificationId)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            $this->checkNotifications();
        }
    }

    // Méthode pour vérifier si on peut faire une nouvelle demande
    public function getPeutFaireNouvelleDemandeProperty()
    {
        return Emprunt::utilisateurPeutEmprunter(Auth::id());
    }

    public function exportExcel()
    {
        $export = new EmpruntsExport($this->emprunts->getCollection(), array_keys(array_filter($this->exportColumns)));

        return Excel::download($export, 'emprunts_' . date('Y-m-d') . '.xlsx');
    }

    public function exportPdf()
    {
        $data = [
            'emprunts' => $this->emprunts->getCollection(),
            'columns' => array_keys(array_filter($this->exportColumns)),
            'filters' => [
                'search' => $this->search,
                'status' => $this->filterStatus,
                'type_amortissement' => $this->filterTypeAmortissement,
            ],
        ];

        $pdf = PDF::loadView('exports.emprunts-pdf', $data)->setPaper('a4', 'landscape');

        return response()->streamDownload(fn() => print $pdf->output(), 'emprunts_' . date('Y-m-d') . '.pdf');
    }

    public function importExcel()
    {
        $this->validate([
            'importFile' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            // Utilisez la classe avec le bon namespace
            Excel::import(new \App\Imports\EmpruntsImport(), $this->importFile);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Importation réussie! ' . count(session()->get('import_count', [])) . ' emprunt(s) importé(s).',
            ]);

            $this->showImportModal = false;
            $this->importFile = null;

            // Rafraîchir les données
            $this->resetPage();
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Erreur lors de l\'importation: ' . $e->getMessage(),
            ]);
        }
    }

    public function empruntCreate()
    {
        $this->dispatch('amortissement-create');
    }
};
?>
<div>
    <livewire:pages::amortissement.create />

    <!-- Inclure le composant de notification bell -->
    @auth
        <livewire:notification-bell />
    @endauth

    <flux:main container>
        <!-- Barre d'outils supérieure -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <!-- Recherche -->
            <div class="w-full md:w-auto">
                <flux:input wire:model.live.debounce.500ms="search" icon="magnifying-glass"
                    placeholder="Rechercher par montant, référence ou motif..." class="w-full md:w-64" />
            </div>

            <!-- Filtres -->
            <div class="flex flex-wrap items-center gap-2">
                <flux:select wire:model.live="filterStatus" size="sm" placeholder="Statut" class="w-full md:w-40">
                    <option value="">Statuts</option>
                    @foreach ($this->statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="filterTypeAmortissement" size="sm" placeholder="Type d'amortissement"
                    class="w-full md:w-48">
                    <option value="">Types</option>
                    @foreach ($this->typeAmortissementOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </flux:select>

                <flux:dropdown>
                    <flux:button variant="ghost" icon="funnel" size="sm">
                        Plus de filtres
                    </flux:button>
                    <flux:menu class="w-64 p-4">
                        <div class="space-y-4">
                            <flux:input wire:model.live.debounce.500ms="filters.montant_min" type="number"
                                placeholder="Montant min" size="sm" />
                            <flux:input wire:model.live.debounce.500ms="filters.montant_max" type="number"
                                placeholder="Montant max" size="sm" />
                            <flux:input wire:model.live.debounce.500ms="filters.duree_mois_min" type="number"
                                placeholder="Durée min (mois)" size="sm" />
                            <flux:input wire:model.live.debounce.500ms="filters.duree_mois_max" type="number"
                                placeholder="Durée max (mois)" size="sm" />
                            <flux:button wire:click="applyFilters" variant="primary" size="sm" class="w-full">
                                Appliquer
                            </flux:button>
                            <flux:button wire:click="resetFilters" variant="ghost" size="sm" class="w-full">
                                Réinitialiser
                            </flux:button>
                        </div>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>

        <!-- Sélection du nombre d'éléments par page -->
        <div class="flex justify-between mb-4 gap-3.5">
            <div class="w-1/2">
                @if (!$this->peutFaireNouvelleDemande)
                    <flux:callout variant="warning" icon="exclamation-circle"
                        heading=" Vous avez déjà un emprunt en cours." />
                @endif
            </div>

            <div class="flex items-center gap-2">
                <flux:select wire:model.live="perPage" size="sm" class="w-32">
                    <option value="5">5 par page</option>
                    <option value="10">10 par page</option>
                    <option value="25">25 par page</option>
                    <option value="50">50 par page</option>
                    <option value="100">100 par page</option>
                </flux:select>
                <flux:separator vertical />
                <flux:dropdown>
                    <flux:button icon="arrow-down-tray" size="sm">
                        Exporter
                    </flux:button>
                    <flux:menu>
                        <flux:menu.item icon="document-arrow-down" wire:click="exportExcel"
                            wire:loading.attr="disabled">
                            Excel (.xlsx)
                        </flux:menu.item>
                        <flux:menu.item icon="document-text" wire:click="exportPdf" wire:loading.attr="disabled">
                            PDF
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                @if ($this->peutFaireNouvelleDemande)
                    <flux:button class="text-white" wire:click="empruntCreate" variant="primary" icon="plus"
                        size="sm">
                        Nouvel emprunt
                    </flux:button>
                @else
                    <flux:button :disabled="!$this->peutFaireNouvelleDemande" variant="primary" color="red"
                        size="sm" title="Vous avez déjà un emprunt en cours">
                        Emprunt non disponible
                    </flux:button>
                @endif


            </div>
        </div>

        <!-- Tableau -->
        <flux:table :paginate="$this->emprunts">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'montant_emprunt'" :direction="$sortDirection"
                    wire:click="sort('montant_emprunt')">
                    Montant
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'date_debut'" :direction="$sortDirection"
                    wire:click="sort('date_debut')">
                    Début
                </flux:table.column>
                <flux:table.column>Fin</flux:table.column>
                <flux:table.column>Durée</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'type_amortissement'" :direction="$sortDirection"
                    wire:click="sort('type_amortissement')">
                    Type
                </flux:table.column>
                <flux:table.column>Fréquence</flux:table.column>
                <flux:table.column>Taux</flux:table.column>
                <flux:table.column>Taux mensuel</flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                    wire:click="sort('status')">
                    Statut
                </flux:table.column>
                <flux:table.column>Notifications</flux:table.column>
                <flux:table.column class="w-24">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows wire:transition>
                @foreach ($this->emprunts as $emprunt)
                    <flux:table.row :key="$emprunt->id">
                        <flux:table.cell variant="strong">
                            {{ number_format($emprunt->montant_emprunt, 0, ',', ' ') }} USD
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $emprunt->date_debut->format('d/m/Y') }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $emprunt->date_fin_remboursement->format('d/m/Y') }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $emprunt->duree_formatee }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge rounded
                                :color="$emprunt->type_amortissement === 'constant' ? 'green' : 'blue'">
                                {{ $emprunt->type_amortissement === 'constant' ? 'Constant' : 'Décroissant' }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge rounded color="zinc">
                                {{ ucfirst($emprunt->frequence_paiement) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($emprunt->taux_interet_annuel)
                                {{ $emprunt->taux_interet_annuel }}%
                            @else
                                <span class="text-gray-400">À définir</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($emprunt->taux_interet_mensuel)
                                {{ $emprunt->taux_interet_mensuel }}%
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @switch($emprunt->status)
                                @case('approuve')
                                    <flux:badge rounded color="green">
                                        {{ $this->statusOptions[$emprunt->status] ?? $emprunt->status }}
                                    </flux:badge>
                                @break

                                @case('en_attente')
                                    <flux:badge rounded color="yellow">
                                        {{ $this->statusOptions[$emprunt->status] ?? $emprunt->status }}
                                    </flux:badge>
                                @break

                                @case('rejete')
                                    <flux:badge rounded color="red">
                                        {{ $this->statusOptions[$emprunt->status] ?? $emprunt->status }}
                                    </flux:badge>
                                @break

                                @case('termine')
                                    <flux:badge rounded color="blue">
                                        {{ $this->statusOptions[$emprunt->status] ?? $emprunt->status }}
                                    </flux:badge>
                                @break

                                @case('defaut')
                                    <flux:badge rounded color="orange">
                                        {{ $this->statusOptions[$emprunt->status] ?? $emprunt->status }}
                                    </flux:badge>
                                @break

                                @default
                                    <flux:badge rounded color="zinc">
                                        {{ $this->statusOptions[$emprunt->status] ?? $emprunt->status }}
                                    </flux:badge>
                            @endswitch
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($emprunt->notifie_approuve && !$emprunt->notifie_fonds_disponibles)
                                <div class="flex items-center gap-1">
                                    <flux:icon.bell-alert class="w-4 h-4 text-green-500"
                                        title="Emprunt approuvé - à signer" />
                                    <span class="text-xs text-green-600">À signer</span>
                                </div>
                            @elseif($emprunt->notifie_fonds_disponibles)
                                <div class="flex items-center gap-1">
                                    <flux:icon.banknotes class="w-4 h-4 text-blue-500" title="Fonds disponibles" />
                                    <span class="text-xs text-blue-600">Fonds dispo</span>
                                </div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal">
                                </flux:button>
                                <flux:menu>
                                    <flux:menu.item icon="eye" wire:click="voirDetails({{ $emprunt->id }})">
                                        Voir détails
                                    </flux:menu.item>

                                    @if ($emprunt->status === 'en_attente')
                                        <flux:menu.item icon="x-circle" variant="danger"
                                            wire:click="annulerEmprunt({{ $emprunt->id }})"
                                            wire:confirm="Voulez-vous vraiment annuler cet emprunt?">
                                            Annuler
                                        </flux:menu.item>
                                    @endif

                                    @if ($emprunt->status === 'approuve' && !$emprunt->notifie_fonds_disponibles)
                                        <flux:menu.item icon="document-text">
                                            Signer contrat
                                        </flux:menu.item>
                                    @endif

                                    @if ($emprunt->status === 'en_cours')
                                        <flux:menu.item icon="currency-euro">
                                            Payer échéance
                                        </flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:main>
</div>
