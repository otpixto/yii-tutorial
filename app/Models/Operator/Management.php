<?php

namespace App\Models\Operator;

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
        return $this->hasMany( 'App\Models\Operator\Address' );
    }

    public static function create ( array $attributes = [] )
    {
        $new = new Management( $attributes );
        $new->save();
        return $new;
    }

}
