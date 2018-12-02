<?php

namespace App\Models;

class Role extends \Iphome\Permission\Models\Role
{
	
	public $guarded = [ 
		'id'
	];
	
    protected $fillable = [ 
		'code', 
		'name', 
		'guard' 
	];
	
	public function scopeMine ( $query )
    {
        return $query
            ->where( function ( $q )
            {
                return $q
                    ->whereNull( 'provider_id' )
                    ->orWhereHas( 'provider', function ( $provider )
                    {
                        return $provider
                            ->mine()
                            ->current();
                    });
            });
    }
	
}