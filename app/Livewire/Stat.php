<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Filament\Widgets\EmpruntOverview;

class Stat extends Component
{
    use WithPagination;

    #[Computed]
    public function stats()
    {
        $statsWidgets = new EmpruntOverview();
        return $statsWidgets->getStats();
    }

    public function render()
    {
        return view('livewire.stat', [
            'stats' => $this->stats(),
        ]);
    }
}
