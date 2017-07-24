<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Management extends Model
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

    public static function create ( array $attributes = [] )
    {
        $new = new Management( $attributes );
        $new->save();
        return $new;
    }

}
