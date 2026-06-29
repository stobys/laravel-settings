<?php

namespace YourVendor\Settings\Facades;

use Illuminate\Support\Facades\Facade;
use YourVendor\Settings\SettingsManager;

/**
 * @method static mixed  get(string $key, mixed $default = null)
 * @method static void   set(string $key, mixed $value)
 * @method static void   setMany(array $settings)
 * @method static bool   has(string $key)
 * @method static void   forget(string $key)
 * @method static array  all()
 * @method static void   clearCache()
 * @method static void   setDefault(string $key, mixed $value)
 * @method static \YourVendor\Settings\SettingsManager for(int|string|null $userId)
 * @method static \YourVendor\Settings\SettingsManager forCurrentUser()
 *
 * @see \YourVendor\Settings\SettingsManager
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return SettingsManager::class;
    }
}
