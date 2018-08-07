<?php

namespace App\Models;

class BuildingType extends BaseModel
{

    protected $table = 'buildings_types';
    public static $_table = 'buildings_types';

    public static $name = 'Тип здания';

    protected $fillable = [
        'name',
    ];

    public function buildings ()
    {
        return $this->hasMany('App\Models\Building');
    }

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }
	
	public function scopeMine ( $query )
    {
        return $query
            ->whereHas( 'provider', function ( $provider )
            {
                return $provider
                    ->mine()
                    ->current();
            });
    }

}
