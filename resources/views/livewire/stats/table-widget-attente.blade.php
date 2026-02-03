<div class="mt-3">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Tableau d\'emprunts') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Tableau d\'emprunts en attente') }}
        </flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    <!-- Tableau -->
    <flux:card class="pt-1 bg-zinc-50 dark:bg-zinc-700">
        <flux:table>
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
                            <flux:badge rounded :color="$emprunt->type_amortissement === 'constant' ? 'green' : 'blue'">
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
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
