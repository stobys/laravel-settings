<?php

namespace SylveK\LaravelSettings\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Setting extends Eloquent
{

    // -- the database table used by the model.
    protected $table = 'generic';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'key', 'value'
    ];

    // -- the attributes that are not mass assignable.
    // protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    protected $guarded = ['id'];

    public function scopeFilter($query)
    {
        return $query;
    }

    public function scopeSort($query)
    {
        return $query;
    }
}
