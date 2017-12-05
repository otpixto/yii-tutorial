<?php

namespace App\Models;

class Address extends BaseModel
{

    protected $table = 'addresses';

    public static $name = 'Адрес';

    public static $rules = [
        'region_id'             => 'required|integer',
        'name'                  => 'required|string|max:255',
    ];

    protected $fillable = [
        'region_id',
        'name',
    ];

    public function managements ()
    {
        return $this->belongsToMany( 'App\Models\Management', 'managements_addresses' );
    }

    public function types ()
    {
        return $this->belongsToMany( 'App\Models\Type', 'addresses_types' );
    }

    public function region ()
    {
        return $this->belongsTo( 'App\Models\Region' );
    }

    public function getAddress ()
    {
        //return $this->address . ' д. ' . $this->home;
        return $this->name;
    }

    public function scopeMine ( $query )
    {
        if ( ! \Auth::user()->can( 'supervisor.all_regions' ) )
        {
            $query
                ->whereHas( 'region', function ( $q )
                {
                    return $q
                        ->mine();
                });
        }
        return $query;
    }

}
