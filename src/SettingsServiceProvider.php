<?php

namespace SylveK\LaravelSettings;

use Illuminate\Support\ServiceProvider;

use SylveK\LaravelSettings\Console\SettingGetCommand;
use SylveK\LaravelSettings\Console\SettingSetCommand;

class SettingsServiceProvider extends ServiceProvider
{
    // -- Indicates if loading of the provider is deferred.
    protected $defer = false;

    // -- Bootstrap the application events.
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/settings.php' => config_path('settings.php')
        ]);
        $this->publishes([
            __DIR__ . '/migrations/2020_06_04_020453_create_settings_table.php' => database_path('migrations/' . date('Y_m_d_His') . '_create_settings_table.php')
        ], 'migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SettingGetCommand::class,
                SettingSetCommand::class,
            ]);
        }
    }

    // -- Register the service provider.
    public function register()
    {
        $this -> mergeConfigFrom(__DIR__ .'/config/settings.php', 'settings');

        $this->app->singleton('settings', function ($app) {
            $config = $app->config->get('settings', [
                'cache_file' => storage_path('settings.json'),
                'db_table'   => 'settings'
            ]);

            return new Settings($app['db'], new Cache($config['cache_file']), $config);
        });

    }

    // -- Get the services provided by the provider.
    public function provides()
    {
        return array ('settings');
    }
}
