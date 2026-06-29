<?php

namespace stobys\LaravelSettings\Facades;

use Illuminate\Support\Facades\Facade;
use stobys\LaravelSettings\SettingsManager;

/**
 * @method static mixed  get(string $key, mixed $default = null)
 * @method static void   set(string $key, mixed $value)
 * @method static void   setMany(array $settings)
 * @method static bool   has(string $key)
 * @method static void   forget(string $key)
 * @method static array  all()
 * @method static void   clearCache()
 * @method static void   setDefault(string $key, mixed $value)
 * @method static \stobys\LaravelSettings\SettingsManager for(int|string|null $userId)
 * @method static \stobys\LaravelSettings\SettingsManager forCurrentUser()
 *
 * @see \stobys\LaravelSettings\SettingsManager
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingsManager::class;
    }
}
