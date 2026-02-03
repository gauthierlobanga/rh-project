<?php

use App\Livewire\MesNotifications;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::home')->name('home');
Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::livewire('/amortissement', 'pages::amortissement.list')->name('amortissement.list');
Route::livewire('/cotisation', 'pages::cotisation.list')->name('cotisation.list');

Route::middleware(['auth'])->group(function () {
    Route::get('/mes-notifications', MesNotifications::class)
        ->name('mes-notifications');
});
require __DIR__.'/settings.php';
