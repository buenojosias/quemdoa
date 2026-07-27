<?php

use App\Livewire\User\Profile;
use Illuminate\Support\Facades\Route;
use App\Livewire\Users\Index;

Route::view('/', 'welcome')->name('welcome');

Route::middleware(['auth'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::get('/users', Index::class)->name('users.index');

    Route::get('/user/profile', Profile::class)->name('user.profile');

    Route::get('/campanhas', \App\Livewire\Campaign\Index::class)->name('campaigns.index');
    Route::get('/campanhas/{campaign}', \App\Livewire\Campaign\Show::class)->name('campaigns.show');
    Route::get('/campanhas/{campaign}/sacolas', \App\Livewire\Campaign\Bags::class)->name('campaigns.bags');
});

require __DIR__.'/auth.php';
