<?php

use App\Livewire\Panel\Users\Index;
use App\Livewire\Panel\User\Profile;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');

Route::middleware(['auth'])->name('panel.')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::get('/users', Index::class)->middleware('admin')->name('users.index');
    Route::get('/user/profile', Profile::class)->name('user.profile');
    Route::get('/campanhas', \App\Livewire\Panel\Campaign\Index::class)->name('campaigns.index');
    Route::get('/campanhas/{campaign}', \App\Livewire\Panel\Campaign\Show::class)->name('campaigns.show');
    Route::get('/campanhas/{campaign}/sacolas', \App\Livewire\Panel\Campaign\Bags::class)->name('campaigns.bags');
    Route::get('/campanhas/{campaign}/sacolas/{bag}', \App\Livewire\Panel\Bag\Show::class)->name('campaigns.bags.show');
});

Route::name('public.')->group(function () {
    Route::get('/c/{campaign}', \App\Livewire\Public\Campaign\Show::class)->name('campaigns.show');
    Route::get('/c/{campaign}/obrigado', \App\Livewire\Public\Campaign\BagFinish::class)->name('campaigns.bag.finish');
});

require __DIR__.'/auth.php';
