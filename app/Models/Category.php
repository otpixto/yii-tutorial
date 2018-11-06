<?php

namespace App\Models;

class Category extends BaseModel
{

    protected $table = 'categories';
    public static $_table = 'categories';

    public static $name = 'Категория';

    protected $fillable = [
        'provider_id',
        'name',
        'color',
        'is_pay',
        'emergency',
        'need_act',
        'works',
    ];

    public function types ()
    {
        return $this->hasMany( 'App\Models\Type' );
    }

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

    public function scopeMine ( $query )
    {
        return $query
            ->whereHas( 'provider', function ( $provider )
            {
                return $provider
                    ->mine()
                    ->current();
            });
    }

}
