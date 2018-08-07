<?php

namespace App\Models;

use App\Models\Asterisk\Cdr;

class Customer extends BaseModel
{

    protected $table = 'customers';
    public static $_table = 'customers';

    public static $name = 'Заявитель';

    private $_calls = null;
    private $_limit = null;

    protected $nullable = [
        'provider_id',
        'email',
		'middlename',
		'actual_building_id',
        'actual_flat',
    ];

    protected $fillable = [
        'provider_id',
        'firstname',
        'middlename',
        'lastname',
        'phone',
        'phone2',
        'actual_building_id',
        'actual_flat',
        'email',
    ];

    public function user ()
    {
        return $this->belongsTo( 'App\User', 'phone', 'phone' );
    }

    public function tickets ()
    {
        return $this->hasMany( 'App\Models\Ticket', 'phone', 'phone' );
    }

    public function actualBuilding ()
    {
        return $this->belongsTo('App\Models\Building');
    }

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
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
                ->whereHas( 'queueLogs', function ( $queueLogs )
                {
                    return $queueLogs
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

    public function scopeMine ( $query )
    {
        return $query
            ->whereHas( 'provider', function ( $provider )
            {
                return $provider
                    ->mine()
                    ->current();
            });
    }

    public function scopeName ( $query, $firstname, $middlename, $lastname )
    {
        return $query
            ->where( self::$_table . '.firstname', '=', $firstname )
            ->where( self::$_table . '.middlename', '=', $middlename )
            ->where( self::$_table . '.lastname', '=', $lastname );
    }

    public static function create ( array $attributes = [] )
    {
        $customer = parent::create( $attributes );
        return $customer;
    }
	
	public function edit ( array $attributes = [] )
    {
        if ( ! empty( $attributes[ 'phone' ] ) )
        {
            $attributes[ 'phone' ] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes[ 'phone' ] ), -10 );
        }
        if ( ! empty( $attributes[ 'phone2' ] ) )
        {
            $attributes[ 'phone2' ] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes[ 'phone2' ] ), -10 );
        }
        return parent::edit( $attributes );
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

    public function getShortName ()
    {
        $name = [];
        if ( ! empty( $this->lastname ) )
        {
            $name[] = $this->lastname;
        }
        if ( ! empty( $this->firstname ) )
        {
            $name[] = mb_substr( $this->firstname, 0, 1 ) . '.';
        }
        if ( ! empty( $this->middlename ) )
        {
            $name[] = mb_substr( $this->middlename, 0, 1 ) . '.';
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

    public function getActualAddress ()
    {
        $addr = '';
        if ( $this->actualBuilding )
        {
            $addr .= $this->actualBuilding->name;
        }
        if ( $this->actual_flat )
        {
            $addr .= ', кв. ' . $this->actual_flat;
        }
        return $addr;
    }

}
