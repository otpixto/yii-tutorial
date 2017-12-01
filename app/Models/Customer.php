<?php

namespace App\Models;

use App\Models\Asterisk\Cdr;
use Illuminate\Support\MessageBag;

class Customer extends BaseModel
{

    protected $table = 'customers';

    public static $name = 'Заявитель';

    private $_calls = null;
    private $_limit = null;

    public static $rules = [
        'firstname'             => 'required|max:191',
        'middlename'            => 'nullable|max:191',
        'lastname'              => 'required|max:191',
        'phone'                 => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        'phone2'                => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        'actual_address_id'     => 'nullable|integer',
        'actual_flat'           => 'nullable|string',
        'email'                 => 'nullable|email',
    ];

    protected $nullable = [
        'email',
		'middlename',
		'actual_address_id',
        'actual_flat',
    ];

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'phone',
        'phone2',
        'actual_address_id',
        'actual_flat',
        'email',
    ];

    public function tickets ()
    {
        return $this->hasMany( 'App\Models\Ticket' );
    }

    public function actualAddress ()
    {
        return $this->belongsTo( 'App\Models\Address' );
    }

    public function calls ( $limit = null )
    {
        if ( is_null( $this->_calls ) || $this->_limit != $limit )
        {
            $cdr = Cdr
                ::where( function ( $q )
                {
                    return $q
                        ->whereRaw( 'RIGHT( src, 10 ) = ?', [ $this->phone ] );
                    if ( $this->phone2 )
                    {
                        $q
                            ->orWhereRaw( 'RIGHT( src, 10 ) = ?', [ $this->phone2 ] );
                    }
                })
                ->answered()
                ->incoming()
                ->whereHas( 'queueLog', function ( $q )
                {
                    return $q
                        ->completed();
                })
                ->orderBy( 'uniqueid', 'desc' );
            if ( $limit )
            {
                $cdr->take( $limit );
            }
            $this->_calls = $cdr->get();
        }
        return $this->_calls;
    }

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
	
	public function edit ( array $attributes = [] )
    {
        $attributes['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes['phone'] ), -10 );
        if ( !empty( $attributes['phone2'] ) )
        {
            $attributes['phone2'] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes['phone2'] ), -10 );
        }
        $res = $this->saveLogs( $attributes );
        if ( $res instanceof MessageBag )
        {
            return $res;
        }
        $this->fill( $attributes );
        $this->save();
        return $this;
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
	
	public function getPhones ( $html = false )
    {
        $phone = '+7 (' . mb_substr( $this->phone, 0, 3 ) . ') ' . mb_substr( $this->phone, 3, 3 ) . '-' . mb_substr( $this->phone, 6, 2 ). '-' . mb_substr( $this->phone, 8, 2 );
        if ( $html )
        {
            $phones = '<a href="tel:7' . $this->phone . '" class="inherit">' . $phone . '</a';
        }
        else
        {
            $phones = $phone;
        }
        if ( !empty( $this->phone2 ) )
        {
            $phone2 = '+7 (' . mb_substr( $this->phone2, 0, 3 ) . ') ' . mb_substr( $this->phone2, 3, 3 ) . '-' . mb_substr( $this->phone2, 6, 2 ). '-' . mb_substr( $this->phone2, 8, 2 );
            $phones .= '; ';
            if ( $html )
            {
                $phones .= '<a href="tel:7' . $this->phone . '" class="inherit">' . $phone2 . '</a';
            }
            else
            {
                $phones .= $phone2;
            }
        }
        return $phones;
    }

    public function getAddress ()
    {
        $addr = '';
        if ( $this->actualAddress )
        {
            $addr .= $this->actualAddress->getAddress();
        }
        if ( $this->actual_flat )
        {
            $addr .= ', кв. ' . $this->actual_flat;
        }
        return $addr;
    }

}
