<?php

namespace App\Models\Operator;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{

    protected $table = 'types';

    public static $rules = [
        'name'              => 'required|string|max:255',
    ];

    protected $fillable = [
        'name',
    ];

    public function tickets ()
    {
        return $this->hasMany( 'App\Models\Operator\Ticket' );
    }

    public static function create ( array $attributes = [] )
    {
        $new = new Type( $attributes );
        $new->save();
        return $new;
    }

}
