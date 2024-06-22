<?php

namespace SylveK\LaravelSettings;

use Illuminate\Support\Arr;

/**
 * Class Cache
 *
 * @package SylveK\LaravelSettings
 */
class Cache
{

    // -- Path to cache file
    protected $cacheFile;

    // -- Cached Settings array
    protected $settings;

    // -- Constructor
    public function __construct($cacheFile)
    {
        $this -> cacheFile = $cacheFile;
        $this -> checkCacheFile();

        $this -> settings = $this -> all();
    }

    // -- Sets a value
    public function set(string $key, $value, int $user_id = 0)
    {
        Arr::set($this -> settings, $user_id .'.'. $key, $value);

        $this -> store();

        return $value;
    }

    // -- Gets a value
    public function get(string $key, $default = null, int $user_id = 0)
    {
        return Arr::get($this -> settings, $user_id .'.'. $key, $default);
    }

    // -- Checks if $key is cached
    public function has(string $key, int $user_id = 0)
    {
        return Arr::has($this -> settings, $user_id .'.'. $key);
    }

    // -- Gets all cached settings
    public function all()
    {
        $this -> checkCacheFile();

        $settings = json_decode(file_get_contents($this -> cacheFile), true);
        $results = [];

        foreach ($settings as $user_id => $values) {
            foreach ($values as $key => $value) {
                Arr::set($results, $user_id .'.'. $key, unserialize($value));
            }
        }

        return $results;
    }

    // -- Alias for all() method
    public function getAll()
    {
        return $this -> all();
    }

    // -- Stores all settings to the cache file
    private function store()
    {
        $settings = [];

        foreach ($this -> settings as $user_id => $settings) {
            foreach ($settings as $key => $value) {
                Arr::set($settings, $user_id .'.'. $key, unserialize($value));
            }
        }

        file_put_contents($this -> cacheFile, json_encode($settings));
    }

    // -- Removes a value
    public function forget(string $key, int $user_id = 0)
    {
        Arr::forget($this -> settings, $user_id .'.'. $key);

        $this -> store();
    }

    // -- Removes all values
    public function flush()
    {
        file_put_contents($this -> cacheFile, json_encode([]));

        $this -> settings = [];
    }

    // -- Checks if the cache file exists and creates it if not
    private function checkCacheFile()
    {
        if ( ! file_exists($this -> cacheFile) ) {
            $this -> flush();
        }
    }

}
