 {{-- @dd($stats) --}}
 <div>
     <div class="flex justify-between items-center mb-6">
         <div class="flex items-center gap-2">
             <div class="flex items-center gap-2">
                 <flux:select size="sm" class="">
                     <option>Last 7 days</option>
                     <option>Last 14 days</option>
                     <option selected>Last 30 days</option>
                     <option>Last 60 days</option>
                     <option>Last 90 days</option>
                 </flux:select>

                 <flux:subheading class="max-md:hidden whitespace-nowrap">compared to</flux:subheading>

                 <flux:select size="sm" class="max-md:hidden">
                     <option selected>Previous period</option>
                     <option>Same period last year</option>
                     <option>Last month</option>
                     <option>Last quarter</option>
                     <option>Last 6 months</option>
                     <option>Last 12 months</option>
                 </flux:select>
                 <flux:select size="sm" class="max-md:hidden">
                     <option selected>Previous period</option>
                     <option>Same period last year</option>
                     <option>Last month</option>
                     <option>Last quarter</option>
                     <option>Last 6 months</option>
                     <option>Last 12 months</option>
                 </flux:select>
             </div>

             <flux:separator vertical class="max-lg:hidden mx-2 my-2" />

             <div class="max-lg:hidden flex justify-start items-center gap-2">
                 <flux:subheading class="whitespace-nowrap">Filter by:</flux:subheading>

                 <flux:badge as="button" rounded color="zinc" icon="plus" size="lg">Amount
                 </flux:badge>
                 <flux:badge as="button" rounded color="zinc" icon="plus" size="lg" class="max-md:hidden">
                     Status</flux:badge>
                 <flux:badge as="button" rounded color="zinc" icon="plus" size="lg">More
                     filters...
                 </flux:badge>
             </div>
         </div>
     </div>
     <div class="grid gap-4 grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 mb-3">
         @foreach ($this->stats() as $stat)
             <div
                 class="relative flex-1 rounded-lg px-6 py-4 bg-zinc-50 dark:bg-zinc-700 {{ $loop->iteration > 1 ? 'max-md:hidden' : '' }}  {{ $loop->iteration > 3 ? 'max-lg:hidden' : '' }}">
                 <flux:subheading>{{ $stat->getDescription() }}</flux:subheading>

                 <flux:heading size="xl" class="mb-2">{{ $stat->getValue() }}</flux:heading>

                 <div
                     class="flex items-center gap-1 font-medium text-sm @if ($stat->getValue()) text-{{ $stat->getColor() }}-600 dark:text-green-400 @else text-red-500 dark:text-red-400 @endif">
                     <flux:icon :icon="$stat->getValue() < 10 ? 'arrow-trending-up' : 'arrow-trending-down'"
                         variant="micro" />
                     {{ $stat->getValue() }}
                 </div>

                 <div class="absolute top-0 right-0 pr-2 pt-2">
                     <flux:button icon="ellipsis-horizontal" variant="subtle" size="sm" />
                 </div>
             </div>
         @endforeach
     </div>
 </div>
