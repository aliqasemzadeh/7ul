<?php

use App\Http\Controllers\LinkController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::index')->name('home');

Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'pages::auth.login')->name('login');
});

Route::post('/logout', function () {
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::livewire('/user', 'pages::user.index')->name('user.index');
});

Route::get('/stats/{shortCode}', [LinkController::class, 'stats'])
    ->where('shortCode', '[A-Za-z0-9]{8}')
    ->name('links.stats');

Route::get('/{shortCode}', [LinkController::class, 'redirect'])
    ->where('shortCode', '[A-Za-z0-9]{8}')
    ->name('links.redirect');
