<?php

use App\Http\Controllers\Api\V1\LinkController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth.api_token')->group(function (): void {
    Route::get('/links', [LinkController::class, 'index'])->name('api.v1.links.index');
    Route::post('/links', [LinkController::class, 'store'])->name('api.v1.links.store');
    Route::get('/links/{shortCode}', [LinkController::class, 'show'])
        ->where('shortCode', '[A-Za-z0-9]{8}')
        ->name('api.v1.links.show');
    Route::get('/links/{shortCode}/stats', [LinkController::class, 'stats'])
        ->where('shortCode', '[A-Za-z0-9]{8}')
        ->name('api.v1.links.stats');
});
