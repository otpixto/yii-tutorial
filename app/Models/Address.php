<?php

namespace App\Models;

class Address extends BaseModel
{

    protected $table = 'addresses';

    public static $rules = [
        'name'              => 'required|string|max:255',
    ];

    protected $fillable = [
        'name',
    ];

    public function managements ()
    {
        return $this->belongsToMany( 'App\Models\Management', 'addresses_managements' )
            ->withPivot( [ 'type_id' ] );
    }

}
