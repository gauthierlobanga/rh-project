{{-- resources/views/livewire/notification-bell.blade.php --}}
<div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <!-- Bouton de notification -->
    <button @click="open = !open; $wire.toggleDropdown()"
        class="relative p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300 focus:outline-none transition-colors duration-200 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800"
        aria-label="Notifications" :aria-expanded="open">
        <!-- Icône de cloche -->
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
            </path>
        </svg>

        <!-- Badge de notifications non lues -->
        @if ($unreadCount > 0)
            <span class="absolute top-1 right-1 flex h-5 w-5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span
                    class="relative inline-flex rounded-full h-5 w-5 bg-red-500 text-xs text-white items-center justify-center font-bold">
                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                </span>
            </span>
        @endif
    </button>

    <!-- Dropdown des notifications -->
    <div x-show="open" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 md:w-96 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 overflow-hidden"
        style="display: none;">
        <!-- En-tête -->
        <div
            class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-900">
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Notifications</h3>
                @if ($unreadCount > 0)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $unreadCount }} non {{ $unreadCount > 1 ? 'lues' : 'lue' }}
                    </p>
                @endif
            </div>
            <div class="flex items-center space-x-2">
                @if ($unreadCount > 0)
                    <button wire:click="markAllAsRead"
                        class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 px-2 py-1 rounded hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors"
                        title="Tout marquer comme lu">
                        Tout lire
                    </button>
                @endif
                @if ($notifications->count() > 0)
                    <button wire:click="clearAll"
                        class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 px-2 py-1 rounded hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors"
                        title="Supprimer toutes les notifications"
                        wire:confirm="Êtes-vous sûr de vouloir supprimer toutes les notifications ?">
                        Tout effacer
                    </button>
                @endif
            </div>
        </div>

        <!-- Liste des notifications -->
        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $type = $notification->type;
                    $isUnread = is_null($notification->read_at);
                    $icon = $this->getNotificationIcon($type);
                    $color = $this->getNotificationColor($type);
                @endphp

                <div wire:click="markAsRead('{{ $notification->id }}')"
                    class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900 cursor-pointer transition-colors {{ $isUnread ? 'bg-blue-50 dark:bg-blue-900/10' : '' }}">
                    <div class="flex items-start">
                        <!-- Icon selon le type -->
                        <div class="flex-shrink-0 mr-3 mt-0.5">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center {{ $this->getNotificationIconBg($type) }}">
                                <span class="{{ $icon }} {{ $color }}"></span>
                            </div>
                        </div>

                        <!-- Contenu -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $data['title'] ?? 'Notification' }}
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                {{ $data['message'] ?? '' }}
                            </p>

                            <!-- Actions si disponibles -->
                            @if (isset($data['action_url']) && isset($data['action_text']))
                                <a href="{{ url($data['action_url']) }}"
                                    class="inline-block mt-2 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                    wire:navigate>
                                    {{ $data['action_text'] }} →
                                </a>
                            @endif

                            <!-- Date -->
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <!-- Indicateur non lu -->
                        @if ($isUnread)
                            <div class="flex-shrink-0 ml-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500 block"></span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center">
                    <div
                        class="w-12 h-12 mx-auto bg-gray-100 dark:bg-gray-900 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                            </path>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Aucune notification
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        Vous serez notifié des mises à jour importantes
                    </p>
                </div>
            @endforelse
        </div>

        <!-- Pied de page -->
        @if ($notifications->count() > 0)
            <div
                class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-center bg-gray-50 dark:bg-gray-900">
                <a href="{{ route('mes-notifications') }}"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium"
                    wire:navigate>
                    Voir toutes les notifications
                </a>
            </div>
        @endif
    </div>
    @script
        <script>
            // Écouter les événements de notification
            document.addEventListener('DOMContentLoaded', function() {
                // Écouter les événements Livewire
                Livewire.on('notification-received', () => {
                    // Jouer un son de notification (optionnel)
                    playNotificationSound();

                    // Animer l'icône
                    animateBell();
                });

                // Fonction pour jouer un son de notification
                function playNotificationSound() {
                    try {
                        const audio = new Audio('/notification-sound.mp3');
                        audio.volume = 0.3;
                        audio.play().catch(e => console.log('Audio play failed:', e));
                    } catch (e) {
                        console.log('Notification sound error:', e);
                    }
                }

                // Fonction pour animer la cloche
                function animateBell() {
                    const bell = document.querySelector('[aria-label="Notifications"] svg');
                    if (bell) {
                        bell.classList.add('animate-shake');
                        setTimeout(() => {
                            bell.classList.remove('animate-shake');
                        }, 500);
                    }
                }
            });
        </script>
    @endscript

    <style>
        /* Animation pour la cloche */
        @keyframes shake {

            0%,
            100% {
                transform: rotate(0deg);
            }

            25% {
                transform: rotate(-15deg);
            }

            75% {
                transform: rotate(15deg);
            }
        }

        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }

        /* Pour le line clamp */
        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
    </style>
</div>
