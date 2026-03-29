<?php

use Illuminate\Support\Facades\Route;
use Mevlutcelik\LaravelExplorer\Http\Controllers\ExplorerController;

// Sadece config dosyasında izin verilen ortamlarda rotayı aktif et
if (in_array(app()->environment(), config('laravel-explorer.environments', ['local']))) {
    Route::get(config('laravel-explorer.path', 'explorer'), [ExplorerController::class, 'index'])
        ->middleware(config('laravel-explorer.middleware', ['web']))
        ->name('laravel-explorer.index');
}