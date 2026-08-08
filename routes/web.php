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
    Route::livewire('/user/create', 'pages::user.create')->name('user.create');
    Route::livewire('/user/api', 'pages::user.api')->name('user.api');
    Route::livewire('/user/links/{shortCode}/stats', 'pages::user.stats')
        ->where('shortCode', '[A-Za-z0-9]{8}')
        ->name('user.links.stats');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::livewire('/users', 'pages::admin.user.index')->name('users.index');
        Route::livewire('/links', 'pages::admin.link.index')->name('links.index');
        Route::livewire('/functions', 'pages::admin.function.index')->name('functions.index');
        Route::livewire('/settings', 'pages::admin.setting.index')->name('settings.index');
    });
});

Route::get('/stats/{shortCode}', [LinkController::class, 'stats'])
    ->where('shortCode', '[A-Za-z0-9]{8}')
    ->name('links.stats');

Route::get('/{shortCode}', [LinkController::class, 'redirect'])
    ->where('shortCode', '[A-Za-z0-9]{8}')
    ->name('links.redirect');
