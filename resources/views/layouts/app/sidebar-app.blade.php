<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">
    @php
        $emprunts = \App\Models\Emprunt::where('user_id', auth()->id())->count();
    @endphp
    <flux:sidebar sticky collapsible="mobile"
        class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.header>
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.search placeholder="Search..." />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="home" :href="route('home')" :current="request()->routeIs('home')" wire:navigate>
                {{ __('Home') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="building-library" :href="route('amortissement.list')"
                :current="request()->routeIs('amortissement.list')" wire:navigate>
                {{ __('Blog') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="building-library" :href="route('cotisation.list')"
                :current="request()->routeIs('cotisation.list')" wire:navigate>
                {{ __('Contact') }}
            </flux:sidebar.item>
            <flux:sidebar.item icon="building-library" :href="route('cotisation.list')"
                :current="request()->routeIs('cotisation.list')" wire:navigate>
                {{ __('Help') }}
            </flux:sidebar.item>
            {{-- Gestion d'amortissement --}}
            <flux:sidebar.group wire:transition expandable heading="Amortissement" class="grid cursor-pointer">
                <flux:sidebar.item icon="chart-bar" :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
                <flux:sidebar.item badge:color="{{ $emprunts >= 10 ? 'green' : 'red' }}" badge="{{ $emprunts }}"
                    icon="building-library" :href="route('amortissement.list')"
                    :current="request()->routeIs('amortissement.list')" wire:navigate>
                    {{ __('Amortissement') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="building-library" :href="route('cotisation.list')"
                    :current="request()->routeIs('cotisation.list')" wire:navigate>
                    {{ __('Cotisation') }}
                </flux:sidebar.item>
            </flux:sidebar.group>
            {{-- Gestion d'assurance vie --}}
            <flux:sidebar.group wire:transition expandable heading="Assurance vie" class="grid">
                <flux:sidebar.item icon="chart-bar" :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
                <flux:sidebar.item badge:color="{{ $emprunts >= 10 ? 'green' : 'red' }}" badge="{{ $emprunts }}"
                    icon="building-library" :href="route('amortissement.list')"
                    :current="request()->routeIs('amortissement.list')" wire:navigate>
                    {{ __('Amortissement') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="building-library" :href="route('cotisation.list')"
                    :current="request()->routeIs('cotisation.list')" wire:navigate>
                    {{ __('Cotisation') }}
                </flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:sidebar.spacer />

        <flux:dropdown position="top" align="start" class="max-lg:hidden">
            @auth
                <flux:profile class="cursor-pointer" :initials="auth()->user()->initials()" :name="auth()->user()->name"
                    icon-trailing="chevron-down" />
            @endauth
            <flux:menu>
                <flux:menu.radio.group>
                    @auth
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    @endauth
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                        {{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                        class="w-full cursor-pointer" data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <flux:header class="block! bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <flux:navbar class="lg:hidden w-full">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                @auth
                    <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />
                @endauth

                <flux:menu>
                    <flux:menu.radio.group>
                        @auth
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" />

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                        <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                    </div>
                                </div>
                            </div>
                        @endauth
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:navbar>

        <flux:navbar scrollable class="-mb-px max-lg:hidden">
            <flux:spacer />
            @auth
                <livewire:notification-bell />
            @endauth
            @auth
                <x-desktop-user-menu />
            @endauth
        </flux:navbar>
    </flux:header>

    {{ $slot }}
    <div>
        @livewire('notifications')
    </div>

    @fluxScripts
    @filamentScripts
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>

</body>

</html>
