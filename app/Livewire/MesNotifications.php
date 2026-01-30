<?php

// namespace App\Livewire;

// use Livewire\Component;
// use Livewire\Attributes\Title;
// use Illuminate\Support\Facades\Auth;
// use Livewire\WithPagination;
// use Livewire\Attributes\On;

// #[Title('Mes notifications')]
// class MesNotifications extends Component
// {
//     use WithPagination;


//     public function markAsRead($id)
//     {
//         $notification = Auth::user()->notifications()->findOrFail($id);
//         $notification->markAsRead();

//         $this->dispatch('notification-lue');
//     }

//     public function markAllAsRead()
//     {
//         Auth::user()->unreadNotifications->markAsRead();

//         $this->dispatch('toutes-notifications-lues');

//         // Émettre un événement global pour mettre à jour le compteur
//         $this->dispatch('notifications-marked-read');
//     }

//     public function clearAll()
//     {
//         Auth::user()->notifications()->delete();

//         $this->dispatch('notifications-supprimees');

//         // Émettre un événement global
//         $this->dispatch('notifications-cleared');
//     }

//     #[On('notification-received')]
//     public function refresh()
//     {
//         $this->resetPage();
//     }

//     public function render()
//     {
//         $user = Auth::user();

//         $notifications = $user->notifications()
//             ->orderBy('created_at', 'desc')
//             ->paginate(20);

//         return view('livewire.mes-notifications', [
//             'notifications' => $notifications
//         ]);
//     }
// }

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Livewire\Attributes\On;

#[Title('Mes notifications')]
class MesNotifications extends Component
{
    use WithPagination;

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $this->dispatch('notification-lue');
        $this->dispatch('notifications-marked-read');
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);

        $this->dispatch('toutes-notifications-lues');
        $this->dispatch('notifications-marked-read');
        $this->resetPage();
    }

    public function clearAll()
    {
        Auth::user()->notifications()->delete();

        $this->dispatch('notifications-supprimees');
        $this->dispatch('notifications-cleared');
        $this->resetPage();
    }

    #[On('notification-received')]
    public function refresh()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->latest('created_at')
            ->paginate(20);

        return view('livewire.mes-notifications', [
            'notifications' => $notifications,
            'unreadCount' => $user->unreadNotifications()->count()
        ]);
    }
}
