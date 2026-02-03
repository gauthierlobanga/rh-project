<?php

namespace App\Livewire;

use Flux\Flux;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificationBell extends Component
{
    public $notifications;
    public $unreadCount = 0;
    public $showDropdown = false;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function notificationOpen()
    {
        $this->dispatch('notification-bell');
    }
    public function notificationClose()
    {
        $this->dispatch('notification-bell-close');
    }

    #[On('notification-bell')]
    public function openNotification()
    {
        Flux::modal('notification-bell')->show();
    }
    #[On('notification-bell-close')]
    public function closeNotification()
    {
        Flux::modal('notification-bell')->close();
    }

    public function loadNotifications()
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Récupérer les notifications (10 dernières)
            $this->notifications = $user->notifications()
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            // Compter les notifications non lues
            $this->unreadCount = $user->unreadNotifications()
                ->whereIn('type', [
                    'App\Notifications\EmpruntApprouve',
                    'App\Notifications\ArgentDisponible',
                    'App\Notifications\EcheanceProchaine',
                    'App\Notifications\EmpruntRefuse',
                    'App\Notifications\PaiementEffectue',
                    'App\Notifications\PaiementEnRetard'
                ])
                ->count();
        } else {
            $this->notifications = collect();
            $this->unreadCount = 0;
        }
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;

        // Si on ouvre le dropdown, charger les notifications
        if ($this->showDropdown) {
            $this->loadNotifications();
        }
    }

    public function markAsRead($notificationId)
    {
        if (Auth::check()) {
            $notification = DatabaseNotification::find($notificationId);

            // Vérifier que la notification appartient bien à l'utilisateur
            if ($notification && $notification->notifiable_id === Auth::id()) {
                $notification->markAsRead();
                $this->loadNotifications();
            }
        }
    }

    public function markAllAsRead()
    {
        if (Auth::check()) {
            Auth::user()->unreadNotifications()->update(['read_at' => now()]);
            $this->loadNotifications();

            // Émettre un événement pour notifier les autres composants
            $this->dispatch('notifications-marked-read');
        }
    }

    public function clearAll()
    {
        if (Auth::check()) {
            Auth::user()->notifications()->delete();
            $this->loadNotifications();

            $this->dispatch('notifications-cleared');
        }
    }

    #[On('notification-received')]
    public function refreshNotifications()
    {
        $this->loadNotifications();
    }

    #[On('notifications-updated')]
    public function handleNotificationsUpdated($count)
    {
        $this->unreadCount = $count;
        $this->loadNotifications();
    }

    // Ajoutez ces méthodes au composant NotificationBell (dans la classe PHP)

    private function getNotificationIcon($type)
    {
        return match ($type) {
            'App\Notifications\EmpruntApprouve' => 'heroicon-o-check-circle',
            'App\Notifications\ArgentDisponible' => 'heroicon-o-banknotes',
            'App\Notifications\EcheanceProchaine' => 'heroicon-o-calendar',
            'App\Notifications\EmpruntRefuse' => 'heroicon-o-x-circle',
            'App\Notifications\PaiementEffectue' => 'heroicon-o-credit-card',
            'App\Notifications\PaiementEnRetard' => 'heroicon-o-clock',
            default => 'heroicon-o-information-circle'
        };
    }

    private function getNotificationColor($type)
    {
        return match ($type) {
            'App\Notifications\EmpruntApprouve' => 'text-green-600 dark:text-green-400',
            'App\Notifications\ArgentDisponible' => 'text-blue-600 dark:text-blue-400',
            'App\Notifications\EcheanceProchaine' => 'text-yellow-600 dark:text-yellow-400',
            'App\Notifications\EmpruntRefuse' => 'text-red-600 dark:text-red-400',
            'App\Notifications\PaiementEffectue' => 'text-green-600 dark:text-green-400',
            'App\Notifications\PaiementEnRetard' => 'text-orange-600 dark:text-orange-400',
            default => 'text-gray-600 dark:text-gray-400'
        };
    }

    private function getNotificationIconBg($type)
    {
        return match ($type) {
            'App\Notifications\EmpruntApprouve' => 'bg-green-100 dark:bg-green-900/30',
            'App\Notifications\ArgentDisponible' => 'bg-blue-100 dark:bg-blue-900/30',
            'App\Notifications\EcheanceProchaine' => 'bg-yellow-100 dark:bg-yellow-900/30',
            'App\Notifications\EmpruntRefuse' => 'bg-red-100 dark:bg-red-900/30',
            'App\Notifications\PaiementEffectue' => 'bg-green-100 dark:bg-green-900/30',
            'App\Notifications\PaiementEnRetard' => 'bg-orange-100 dark:bg-orange-900/30',
            default => 'bg-gray-100 dark:bg-gray-900'
        };
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
