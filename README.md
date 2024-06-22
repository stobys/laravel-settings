[![Latest Stable Version](http://poser.pugx.org/stobys/laravel-settings/v?style=for-the-badge)](https://packagist.org/packages/stobys/laravel-settings)
[![Total Downloads](https://poser.pugx.org/stobys/laravel-settings/downloads?style=for-the-badge)](https://packagist.org/packages/stobys/laravel-settings) 
[![License](https://poser.pugx.org/stobys/laravel-settings/license?style=for-the-badge)](https://packagist.org/packages/stobys/laravel-settings)

# Laravel-Settings
Laravel Settings (Database + Cache) - basically clone of https://github.com/efriandika/laravel-settings


## How to Install
Require this package with composer ([Packagist](https://packagist.org/packages/stobys/laravel-settings)) using the following command:

    composer require stobys/laravel-settings

or modify your `composer.json`:

       "require": {
          "stobys/laravel-settings": "1.*"
       }

then run `composer update`:

After updating composer, ServiceProvider and Facade class will be registered automatically, no need to register them manually.

Publish the config and migration files now (Attention: This command will not work if you don't follow previous instruction):

    $ php artisan vendor:publish --provider="SylveK\LaravelSettings\SettingsServiceProvider" --force

Change `config/settings.php` according to your needs.

Create the `settings` table.

    $ php artisan migrate


## How to Use it?

Set a value

    Settings::set('key', 'value');

Get a value

    $value = Settings::get('key');

Get a value with Default Value.

    $value = Settings::get('key', 'Default Value');

> Note: If key is not found (null) in cache or settings table, it will return default value

Get a value via an helper

    $value = settings('key');
    $value = settings('key', 'default value');

Forget a value

    Settings::forget('key');

Forget all values

    Settings::flush();

## Fallback to Laravel Config

How to activate?

    // Change your config/settings.php
    'fallback'   => true

Example

    /*
     * If the value with key => mail.host is not found in cache or DB of Larave Settings
     * it will return same value as config::get('mail.host');
     */
    Settings::get('mail.host');

> Note: It will work if default value in laravel setting is not set

### License

The Laravel Settings is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

