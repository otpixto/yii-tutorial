<?php

namespace App\Models;

class Device extends BaseModel
{

    protected $fillable = [
        'username',
        'password',
        'push_id'
    ];

    protected $nullable = [
        'push_id',
    ];

    public static function create ( array $attributes = [] ) : Device
    {
        $attributes[ 'password' ] = bcrypt( $attributes[ 'password' ] );
        return parent::create( $attributes );
    }

}