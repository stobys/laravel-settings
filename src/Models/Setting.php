<?php

namespace SylveK\LaravelSettings\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Setting extends Eloquent
{

    // -- the database table used by the model.
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'setting_key', 'setting_value'
    ];

    // -- the attributes that are not mass assignable.
    protected $guarded = ['id'];
}
