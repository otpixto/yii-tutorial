<?php

namespace App\Models;

class ProviderPhone extends BaseModel
{

    protected $table = 'providers_phones';
    public static $_table = 'providers_phones';

    public static $name = 'Внутренний номер поставщика';

    public static $rules = [
        'region_id'             => 'required|integer',
        'phone'                 => 'required|regex:/\d/|max:10'
    ];

    protected $fillable = [
        'region_id',
        'phone',
    ];

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

}
