<?php

namespace App\Models\Operator;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    protected $table = 'customers';

    public static $rules = [
        'firstname'         => 'required|max:191',
        'middlename'        => 'nullable|max:191',
        'lastname'          => 'nullable|max:191',
        'phone'             => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        'phone2'            => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
    ];

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'phone',
        'phone2'
    ];

    public static function create ( array $attributes = [] )
    {

        $attributes['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes['phone'] ), -10 );
        if ( !empty( $attributes['phone2'] ) )
        {
            $attributes['phone2'] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes['phone2'] ), -10 );
        }

        $customer = new Customer( $attributes );
        $customer->save();

        return $customer;

    }

    public function getName ()
    {
        $name = [];
        if ( !empty( $this->lastname ) )
        {
            $name[] = $this->lastname;
        }
        if ( !empty( $this->firstname ) )
        {
            $name[] = $this->firstname;
        }
        if ( !empty( $this->middlename ) )
        {
            $name[] = $this->middlename;
        }
        return implode( ' ', $name );
    }

}
