<?php

namespace App\Models;

class BuildingGroup extends BaseModel
{

    protected $table = 'buildings_groups';
    public static $_table = 'buildings_groups';

    public static $name = 'Группа зданий';

    protected $fillable = [
        'provider_id',
        'name',
    ];

    public function provider ()
    {
        return $this->belongsTo( Provider::class );
    }

    public function buildings ()
    {
        return $this->belongsToMany( Building::class, 'group_building' );
    }

    public function scopeMine ( $query )
    {
        return $query
            ->mineProvider();
    }

}
