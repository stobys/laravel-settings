<?php

namespace SylveK\LaravelSettings;

use Illuminate\Support\ServiceProvider;

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
            __DIR__ . '/database/migrations/' => base_path('/database/migrations')
        ]);

        // if ($this->app->runningInConsole()) {
            $this->commands([
                Console\SettingGetCommand::class,
                Console\SettingSetCommand::class,
            ]);
        // }
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
