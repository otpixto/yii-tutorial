<?php

namespace App\Models;

class Management extends BaseModel
{

    protected $table = 'managements';

    public static $rules = [
        'name'              => 'required|string|max:255',
    ];

    protected $fillable = [
        'name',
        'address',
        'phone'
    ];

    public function addresses ()
    {
        return $this->belongsToMany( 'App\Models\Address', 'addresses_managements' )
            ->withPivot( [ 'type_id' ] );
    }

    public function tickets ()
    {
        return $this->hasMany( 'App\Models\TicketManagement' );
    }

    public static function create ( array $attributes = [] )
    {
        $new = new Management( $attributes );
        $new->has_contract = !empty( $attributes['has_contract'] ) ? 1 : 0;
        $new->save();
        return $new;
    }

    public function edit ( array $attributes = [] )
    {

        $this->fill( $attributes );
        $this->has_contract = !empty( $attributes['has_contract'] ) ? 1 : 0;
        $this->save();

    }

}
