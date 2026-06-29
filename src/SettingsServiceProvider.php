<?php

namespace YourVendor\Settings;

use Illuminate\Support\ServiceProvider;
use YourVendor\Settings\Cache\FileSettingsCache;
use YourVendor\Settings\Contracts\SettingsRepository;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/settings.php',
            'settings'
        );

        // FileSettingsCache – singleton (jeden plik, jeden stan w ramach requesta)
        $this->app->singleton(FileSettingsCache::class, function ($app) {
            $path = $app['config']->get('settings.cache.path');
            return new FileSettingsCache($path);
        });

        // Repository – singleton
        $this->app->singleton(SettingsRepository::class, function ($app) {
            return new DatabaseSettingsRepository(
                $app->make(FileSettingsCache::class),
                $app['config']->get('settings')
            );
        });

        // Manager – singleton (kontekst per-user jest immutable clone, więc singleton jest bezpieczny)
        $this->app->singleton(SettingsManager::class, function ($app) {
            return new SettingsManager(
                $app->make(SettingsRepository::class)
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publikuj config
            $this->publishes([
                __DIR__ . '/../config/settings.php' => config_path('settings.php'),
            ], 'settings-config');

            // Publikuj migrację
            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'settings-migrations');

            // Rejestruj komendę Artisan
            $this->commands([
                Console\SettingsClearCacheCommand::class,
            ]);
        }

        // Ładuj migrację automatycznie (jeśli nie opublikowano)
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
