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

    // -- DB Fields
    protected $fields = [
        'key'   => 'setting_key',
        'val'   => 'setting_value'
    ];

    // -- Constructor
    public function __construct(DatabaseManager $database, Cache $cache, $config = array ())
    {
        $this -> database = $database;
        $this -> config   = $config;
        $this -> cache    = $cache;

        $this -> fields['key'] = config('settings.db_field_key', 'setting_key');
        $this -> fields['val'] = config('settings.db_field_value', 'setting_value');
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
            $user = \App\Models\User::whereUsername($user) -> firstOrFail();
            $this -> user_id = $user -> id;
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
                        -> where($this -> fields['key'], $key)
                        -> first([$this -> fields['val']]);

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
                        -> where($this -> fields['key'], $key)
                        -> count([$this -> fields['val']]);

        return $count > 0;
    }

    // -- Store value into registry
    public function set($key, $value, $user_id = 0)
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
                        -> where($this -> fields['key'], $key)
                        -> first();

        if (is_null($setting)) {
            $this -> database -> table($this->config['db_table'])
                        -> insert([
                            'user_id' => $user_id,
                            $this -> fields['key'] => $key,
                            $this -> fields['val'] => $value,
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
                           -> where($this -> fields['key'], $key)
                           -> update([$this -> fields['val'] => $value]);
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
                -> where($this -> fields['key'], $key)
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
