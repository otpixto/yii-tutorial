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
        return $this->hasMany(Building::class );
    }
	
	public function scopeMine ( $query )
    {
        return $query
            ->mineProvider();
    }

}
