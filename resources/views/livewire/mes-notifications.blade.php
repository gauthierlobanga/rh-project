<div>
    {{-- Dans votre layout (ex: app.blade.php, layout.blade.php, etc.) --}}
    @auth
        <div class="hidden md:flex items-center space-x-4">
            <!-- Composant de notification -->
            <livewire:notification-bell />

            <!-- Menu utilisateur existant -->
            <flux:dropdown>
                <flux:button variant="ghost" class="flex items-center space-x-2">
                    <flux:avatar :src="auth()->user()->profile_photo_url ?? ''" alt="{{ auth()->user()->name }}" />
                    <span>{{ auth()->user()->name }}</span>
                    <flux:icon.chevron-down class="w-4 h-4" />
                </flux:button>

                <flux:menu>
                    <flux:menu.item href="{{ route('mes-emprunts') }}" icon="banknotes">
                        Mes emprunts
                    </flux:menu.item>
                    <flux:menu.item href="{{ route('mes-notifications') }}" icon="bell">
                        Mes notifications
                        @if (auth()->user()->unreadNotifications()->count() > 0)
                            <flux:badge color="red" size="sm" class="ml-2">
                                {{ auth()->user()->unreadNotifications()->count() }}
                            </flux:badge>
                        @endif
                    </flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item href="{{ route('profile.show') }}" icon="user">
                        Mon profil
                    </flux:menu.item>
                    <flux:menu.item href="{{ route('logout') }}" icon="arrow-right-on-rectangle" method="post">
                        Déconnexion
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    @else
        <div class="hidden md:flex items-center space-x-4">
            <flux:button href="{{ route('login') }}" variant="ghost">
                Connexion
            </flux:button>
            <flux:button href="{{ route('register') }}" variant="primary" class="bg-accent text-white">
                Inscription
            </flux:button>
        </div>
    @endauth
</div>
