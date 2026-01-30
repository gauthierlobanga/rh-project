<x-layouts::app :title="__('Dashboard')">
    <livewire:stat />
    <livewire:stats.progress />
    <flux:spacer />
    {{-- Table avec statut Approuvé --}}
    <livewire:stats.table-widget />
    <flux:spacer />
    {{-- Table avec statut En attente --}}
    <livewire:stats.table-widget-attente />
</x-layouts::app>
