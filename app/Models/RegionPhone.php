<?php

namespace App\Models;

class RegionPhone extends BaseModel
{

    protected $table = 'regions_phones';

    public static $name = 'Внутренний номер региона';

    public static $rules = [
        'region_id'             => 'required|integer',
        'phone'                 => 'required|string|max:10'
    ];

    protected $fillable = [
        'region_id',
        'phone',
    ];

    public function region ()
    {
        return $this->belongsTo( 'App\Models\Region' );
    }

}
