<?php

namespace stobys\Settings\Facades;

use Illuminate\Support\Facades\Facade;
use stobys\Settings\SettingsManager;

/**
 * @method static mixed  get(string $key, mixed $default = null)
 * @method static void   set(string $key, mixed $value)
 * @method static void   setMany(array $settings)
 * @method static bool   has(string $key)
 * @method static void   forget(string $key)
 * @method static array  all()
 * @method static void   clearCache()
 * @method static void   setDefault(string $key, mixed $value)
 * @method static \stobys\Settings\SettingsManager for(int|string|null $userId)
 * @method static \stobys\Settings\SettingsManager forCurrentUser()
 *
 * @see \stobys\Settings\SettingsManager
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingsManager::class;
    }
}
