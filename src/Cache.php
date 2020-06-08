<?php

namespace SylveK\LaravelSettings;

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

        $this -> settings = $this -> getAll();
    }

    // -- Sets a value
    public function set($key, $value, $user_id = 0)
    {
        $user_id = empty($user_id) ? 0 : $user_id;
        array_set($this -> settings, $user_id .'.'. $key, $value);

        $this -> store();

        return $value;
    }

    // -- Gets a value
    public function get($key, $default = null, $user_id = 0)
    {
        return array_get($this -> settings, $user_id .'.'. $key, $default);
    }

    // -- Checks if $key is cached
    public function has($key, $user_id)
    {
        return array_has($this -> settings, $user_id .'.'. $key);
    }

    // -- Gets all cached settings
    public function getAll()
    {
        $settings = json_decode(file_get_contents($this -> cacheFile), true);
        $results = [];

        foreach ($settings as $user_id => $values) {
            foreach ($values as $key => $value) {
                array_set($results, $user_id .'.'. $key, unserialize($value));
            }
        }

        return $results;
    }

    // -- Stores all settings to the cache file
    private function store()
    {
        $settings = [];

        foreach ($this -> settings as $user_id => $settings) {
            foreach ($settings as $key => $value) {
                $settings[$user_id][$key] = serialize($value);
            }
        }

        file_put_contents($this -> cacheFile, json_encode($settings));
    }

    // -- Removes a value
    public function forget($key, $user_id = 0)
    {
        array_forget($this -> settings, $user_id .'.'. $key);

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
