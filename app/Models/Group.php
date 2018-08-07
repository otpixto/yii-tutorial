<?php

namespace App\Models;

class Group extends BaseModel
{

    protected $table = 'groups';
    public static $_table = 'groups';

    public static $name = 'Группа';

    protected $fillable = [
        'provider_id',
        'name',
    ];
	
	public static $rules = [
        'provider_id'	    => 'required|integer',
        'name'				=> 'required|string',
    ];

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

    public function buildings ()
    {
        return $this->belongsToMany( 'App\Models\Building', 'groups_buildings' );
    }

    public function scopeMine ( $query )
    {
        return $query
            ->whereHas( 'provider', function ( $provider )
            {
                return $provider
                    ->mine();
            });
    }

}
