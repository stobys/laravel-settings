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
            $this -> user_id = $user -> id;
        }
        elseif ( is_numeric($user) )
        {
            $this -> user_id = $user;
        }
        else {
            $user = \App\Models\User::whereUsername($user) -> first();
            if ( $user )
            {
                $this -> user_id = $user -> id;
            }
        }

        return $this;
    }

    public function unsetUser()
    {
        $this -> user_id = null;

        return $this;
    }

    public function whichUser()
    {
        return $this -> user_id;
    }

    // -- Value getter
    public function get($key, $default = null, $user_id = null)
    {
        $value = $this -> fetch($key, $user_id);

        if( !is_null($value) )
        {
            return $value;
        }
        elseif( ! is_null($default) )
        {
            return $default;
        }
        elseif( $this -> config['fallback'] )
        {
            return Config::get($key, null);
        }
        else
        {
            return $default;
        }
    }

    // -- Get the setting value
    private function fetch($key, $user_id = null)
    {
        $user_id = is_null($user_id) ? $this -> user_id : $user_id;

        if ($this -> cache -> has($key, $user_id)) {
            return $this -> cache -> get($key, null, $user_id);
        }

        $row = $this -> database -> table($this -> config['db_table'])
                        -> where( function($query) use ($user_id) {
                                if ( $user_id )
                                {
                                    $query -> where('user_id', $user_id);
                                }
                                else {
                                    $query -> whereNull('user_id');
                                }
                            })
                        -> where('setting', $key)
                        -> first(['value']);

        return (!is_null($row)) ? $this -> cache -> set($key, unserialize($row -> value), $user_id) : null;
    }


    // -- Checks if setting exists
    public function has($key)
    {
        $user_id = $this -> user_id;

        if ($this -> cache -> has($key, $user_id)) {
            return true;
        }

        $count = $this -> database -> table($this -> config['db_table'])
                        -> where( function($query) use ($user_id) {
                                if ( $user_id )
                                {
                                    $query -> where('user_id', $user_id);
                                }
                                else {
                                    $query -> whereNull('user_id');
                                }
                            })
                        -> where('setting', $key)
                        -> count(['value']);

        return $count > 0;
    }

    // -- Store value into registry
    public function set($key, $value, $user_id = null)
    {
        $value = serialize($value);

        $setting = $this -> database -> table($this -> config['db_table'])
                        -> where( function($query) use ($user_id) {
                                if ( $user_id )
                                {
                                    $query -> where('user_id', $user_id);
                                }
                                else {
                                    $query -> whereNull('user_id');
                                }
                            })
                        -> where('setting', $key)
                        -> first();

        if (is_null($setting)) {
            $this -> database -> table($this->config['db_table'])
                        -> insert([
                            'user_id' => $user_id,
                            'setting' => $key,
                            'value' => $value,
                        ]);
        }
        else {
            $this -> database -> table($this->config['db_table'])
                            -> where( function($query) use ($user_id) {
                                    if ( $user_id )
                                    {
                                        $query -> where('user_id', $user_id);
                                    }
                                    else {
                                        $query -> whereNull('user_id');
                                    }
                                })
                           -> where('setting', $key)
                           -> update(['value' => $value]);
        }

        $this -> cache -> set($key, unserialize($value), $user_id);

        return $value;
    }


    // -- Remove a setting
    public function forget($key)
    {
        $user_id = $this -> user_id;

        $this -> database -> table($this -> config['db_table'])
                -> where( function($query) use ($user_id) {
                        if ( $user_id )
                        {
                            $query -> where('user_id', $user_id);
                        }
                        else {
                            $query -> whereNull('user_id');
                        }
                    })
                -> where('setting', $key)
                -> delete();

        $this -> cache -> forget($key, $user_id);
    }

    // -- Remove all settings
    public function flush()
    {
        $this -> cache -> flush();

        return $this -> database -> table($this -> config['db_table']) -> delete();
    }

    // -- Get Cache object
    public function cache()
    {
        return $this -> cache;
    }

    // -- Fetch all values
    public function all()
    {
        return $this -> cache -> all();
    }


    private function reloadCache() {
        // -- @TODO : reload cache file from DB
    }
}
