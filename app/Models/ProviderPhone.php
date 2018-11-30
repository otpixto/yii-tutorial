<?php

namespace App\Models;

class ProviderPhone extends BaseModel
{

    protected $table = 'providers_phones';
    public static $_table = 'providers_phones';

    public static $name = 'Внутренний номер поставщика';

    protected $fillable = [
        'provider_id',
        'phone',
        'name',
        'description',
    ];

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

}
