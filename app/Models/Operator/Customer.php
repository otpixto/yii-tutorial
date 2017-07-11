<?php

namespace App\Models\Operator;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    protected $table = 'customers';

    protected $nullable = [
        'phone2'
    ];

    public static $rules = [
        'firstname'         => 'required|string|max:100',
        'middlename'        => 'required|string|max:100',
        'lastname'          => 'required|string|max:100',
        'phone1'            => 'required|string|max:11',
        'phone2'            => 'string|max:11'
    ];

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'phone1',
        'phone2'
    ];

    public static function create ( array $attributes = [] )
    {
        $new = new Customer( $attributes );
        $new->save();
        return $new;
    }

}
