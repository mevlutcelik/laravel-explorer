<?php

namespace Mevlutcelik\LaravelExplorer;

use Illuminate\Support\ServiceProvider;

class LaravelExplorerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 1. Rotaları Yükle
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // 2. View (Blade) Dosyalarını Yükle
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'laravel-explorer');

        // 3. Publish İşlemleri
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/laravel-explorer.php' => config_path('laravel-explorer.php'),
            ], 'laravel-explorer-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/laravel-explorer'),
            ], 'laravel-explorer-views');
        }
    }

    public function register()
    {
        // Config dosyasını birleştir
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-explorer.php', 'laravel-explorer');
    }
}