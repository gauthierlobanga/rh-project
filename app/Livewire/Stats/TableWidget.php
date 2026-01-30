<?php

namespace App\Livewire\Stats;

use Livewire\Component;
use Livewire\WithPagination;

class TableWidget extends Component
{
    use WithPagination;

    public $sortBy = 'montant_emprunt';
    public $sortDirection = 'desc';

    protected function statusOptions(string $status = 'approuve'): string
    {
        return $status;
    }

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[\Livewire\Attributes\Computed]
    public function emprunts()
    {

        return \App\Models\Emprunt::query()
            ->tap(fn($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->where('status', $this->statusOptions())
            ->paginate(5);
    }


    public function render()
    {
        return view('livewire.stats.table-widget');
    }
}
