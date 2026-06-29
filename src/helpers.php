<?php

use stobys\LaravelSettings\Facades\Settings;

if (!function_exists('settings')) {
    function settings(string|array|null $key = null, mixed $default = null): mixed
    {
        // settings() bez argumentów – zwróć cały SettingsManager
        if ($key === null) {
            return app(\stobys\LaravelSettings\SettingsManager::class);
        }

        // settings(['key' => 'value', ...]) – set
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                Settings::set($k, $v);
            }
            return null;
        }

        // settings('key') lub settings('key', 'default') – get
        return Settings::get($key, $default);
    }
}
