<?php

use App\Models\Emprunt;
use Livewire\Component;
use Livewire\Attributes\Computed;

new class extends Component
{
    #[Computed]
    public function emprunts()
    {
        return Emprunt::with(['user', 'echeances', 'paiements'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('montant_emprunt', 'like', '%' . $this->search . '%')
                        ->where('status', 'like', '%' . $this->search . '%')
                        ->where('type_amortissement', 'like', '%' . $this->search . '%')
                        ->where('frequence_paiement', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filters['status'], function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($this->filters['date_debut'], function ($query, $date) {
                $query->where('date_debut', '>=', $date);
            })
            ->when($this->filters['date_fin'], function ($query, $date_fin) {
                $query->where('date_fin', '<=', $date_fin);
            })
            ->when($this->filters['montant_min'], function ($query, $montant) {
                $query->where('montant_emprunt', '>=', $montant);
            })
            ->when($this->filters['montant_max'], function ($query, $montant) {
                $query->where('montant_emprunt', '<=', $montant);
            })
            ->where('user_id', auth()->id())
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }
};
