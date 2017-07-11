<?php

namespace App\Models\Operator;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{

    protected $table = 'addresses';

    protected $nullable = [
        'management_id',
    ];

    public static $rules = [
        'name'              => 'required|string|max:255',
        'management_id'     => 'integer',
    ];

    protected $fillable = [
        'name',
        'management_id'
    ];

    public function management ()
    {
        return $this->hasOne( 'App\Models\Operator\Management' );
    }

    public static function create ( array $attributes = [] )
    {
        $new = new Address( $attributes );
        $new->save();
        return $new;
    }

}
