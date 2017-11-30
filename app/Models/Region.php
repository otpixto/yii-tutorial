<?php

namespace App\Models;

class Region extends BaseModel
{

    protected $table = 'regions';

    public static $name = 'Регион';

    public static $current_region = null;

    public static $rules = [
        'name'                  => 'required|string|max:255'
    ];

    protected $fillable = [
        'name',
    ];

    public function phones ()
    {
        return $this->hasMany( 'App\Models\RegionPhone' );
    }

    public function addresses ()
    {
        return $this->hasMany( 'App\Models\Address' );
    }

    public function managements ()
    {
        return $this->hasMany( 'App\Models\Management' );
    }

    public function users ()
    {
        return $this->belongsToMany( 'App\User', 'users_regions' );
    }

    public function scopeMine ( $query )
    {
        return $query
            ->where( 'domain', '=', \Request::getHost() );
    }

}
