<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;

class AddressManagement extends Model
{

    protected $table = 'addresses_managements';

    protected $fillable = [
        'address_id',
        'type_id',
        'management_id'
    ];

    public function address ()
    {
        return $this->belongsTo( 'App\Models\Address' );
    }

    public function type ()
    {
        return $this->belongsTo( 'App\Models\Type' );
    }

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    public static function create ( array $attributes = [] )
    {
        $address = Address::find( $attributes['address_id'] );
        if ( !$address )
        {
            return new MessageBag(['Адрес не найден']);
        }
        $type = Type::find( $attributes['type_id'] );
        if ( !$type )
        {
            return new MessageBag(['Тип не найден']);
        }
        $management = Management::find( $attributes['management_id'] );
        if ( !$management )
        {
            return new MessageBag(['УК не найдена']);
        }
        $new = new AddressManagement( $attributes );
        $new->save();
        return $new;
    }

}
