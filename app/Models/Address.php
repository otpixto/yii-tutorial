<?php

namespace App\Models;

class Address extends BaseModel
{

    protected $table = 'addresses';

    public static $name = 'Адрес';

    protected $nullable = [
        'guid'
    ];

    protected $fillable = [
        'guid',
        'region_id',
        'name',
    ];

    public function managements ()
    {
        return $this->belongsToMany( 'App\Models\Management', 'managements_addresses' );
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
        return $query
            ->whereHas( 'region', function ( $q )
            {
                return $q
                    ->mine()
                    ->current();
            });
    }

}
