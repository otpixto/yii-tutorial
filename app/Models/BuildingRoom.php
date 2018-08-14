<?php

namespace App\Models;

class BuildingRoom extends BaseModel
{

    protected $table = 'buildings_rooms';
    public static $_table = 'buildings_rooms';

    public static $name = 'Помещение';

    protected $fillable = [
        'building_id',
        'floor',
        'porch',
        'number',
        'living_area',
        'total_area',
    ];

    public function building ()
    {
        return $this->belongsTo('App\Models\Building');
    }
	
	public function scopeMine ( $query )
    {
        return $query
            ->whereHas( 'building', function ( $building )
            {
                return $building
                    ->mine();
            });
    }

    public function getAddress ()
    {
        $addr = '';
        if ( $this->building )
        {
            $addr .= $this->building->name . ' (' . $this->building->buildingType->name . ')';
        }
        return $addr;
    }

}
