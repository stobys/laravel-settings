<?php

namespace SylveK\LaravelSettings;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Config;

/**
 * Class Settings
 * @package Efriandika\LaravelSettings
 */
class Settings
{

    // -- Registry config
    protected $config;

    // -- Database manager instance
    protected $database;

    // -- Cache
    protected $cache;

    // -- User ID
    protected $user_id;

    // -- Constructor
    public function __construct(DatabaseManager $database, Cache $cache, $config = array ())
    {
        $this -> database = $database;
        $this -> config   = $config;
        $this -> cache    = $cache;
    }

    public function setUser( $user )
    {

        if ( $user instanceof \App\Models\User )
        {
            $this -> user = $user -> id;
        }
        elseif ( is_int($user) )
        {
            $this -> user = $user;
        }
        else (
            $user = \App\Models\User::whereUsername($user) -> first();
            if ( $user )
            {
                $this -> user_id = $this -> id;
            }
        )

        return $this;
    }

    public function unsetUser()
    {

        $this -> user_id = null;

        return $this;
    }

    /**
     * Value getter
     *
     * @param  string $key
     * @param  string $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = $this -> fetch($key);

        if( !is_null($value) )
        {
            return $value;
        }
        else if($default != null)
        {
            return $default;
        }
        else if($this -> config['fallback'])
        {
            return Config::get($key, null);
        }
        else
        {
            return $default;
        }
    }

    /**
     * @param $key
     *
     * @return mixed|null
     */
    private function fetch($key)
    {
        if ($this -> cache -> hasKey($key)) {
            return $this -> cache -> get($key);
        }

        $row = $this -> database -> table($this -> config['db_table'])
                        -> where('user_id', $this -> user_id)
                        -> where('key', $key)
                        -> first(['value']);

        return (!is_null($row)) ? $this -> cache -> set($key, unserialize($row -> value)) : null;
    }


    /**
     * Checks if setting exists
     *
     * @param $key
     *
     * @return bool
     */
    public function hasKey($key)
    {
        if ($this->cache->hasKey($key)) {
            return true;
        }

        $count = $this -> database -> table($this -> config['db_table'])
                        -> where('user_id', $this -> user_id)
                        -> where('key', $key)
                        -> count(['value']);

        return $count > 0;
    }

    /**
     * Store value into registry
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @return mixed
     */
    public function set($key, $value)
    {
        $value = serialize($value);

        $setting = $this -> database -> table($this -> config['db_table'])
                        -> where('user_id', $this -> user_id)
                        -> where('key', $key)
                        -> first();

        if (is_null($setting)) {
            $this -> database -> table($this->config['db_table'])
                        -> insert([
                            'user_id' => $this -> user_id
                            'key' => $key,
                            'value' => $value,
                        ]);
        }
        else {
            $this -> database -> table($this->config['db_table'])
                           -> where('user_id', $this -> user_id)
                           -> where('key', $key)
                           -> update(['value' => $value]);
        }

        $this -> cache -> set($key, unserialize($value));

        return $value;
    }


    /**
     * Remove a setting
     *
     * @param  string $key
     *
     * @return void
     */
    public function forget($key)
    {
        $this -> database -> table($this -> config['db_table'])
                -> where('user_id', $this -> user_id)
                -> where('key', $key)
                -> delete();

        $this -> cache -> forget($key);
    }

    /**
     * Remove all settings
     *
     * @return bool
     */
    public function flush()
    {
        $this -> cache -> flush();

        return $this -> database -> table($this -> config['db_table']) -> delete();
    }

    /**
     * Fetch all values
     *
     * @return mixed
     */
    public function getAll()
    {
        return $this -> cache -> getAll();
    }

}
