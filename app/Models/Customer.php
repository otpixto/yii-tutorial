<?php

namespace App\Models;

use App\Models\Asterisk\Cdr;
use App\User;
use Illuminate\Support\Facades\Request;

class Customer extends BaseModel
{

    protected $table = 'customers';
    public static $_table = 'customers';

    public static $name = 'Заявитель';

    private $_calls = null;
    private $_limit = null;

    private $addressTickets = null;
    private $phoneTickets = null;

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
        return $this->belongsTo( User::class, 'phone', 'phone' );
    }

    public function tickets ()
    {
        return $this->hasMany( Ticket::class, 'phone', 'phone' );
    }

    public function actualBuilding ()
    {
        return $this->belongsTo( Building::class );
    }

    public function buildings ()
    {
        return $this->belongsToMany( Building::class, 'customers_buildings' );
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
                } )
                ->answered()
                ->incoming()
                ->whereHas( 'queueLogs', function ( $queueLogs )
                {
                    return $queueLogs
                        ->completed();
                } )
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
            ->mineProvider();
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
            $attributes[ 'phone' ] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes[ 'phone' ] ), - 10 );
        }
        if ( ! empty( $attributes[ 'phone2' ] ) )
        {
            $attributes[ 'phone2' ] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes[ 'phone2' ] ), - 10 );
        }
        return parent::edit( $attributes );
    }

    public function getName ()
    {
        $name = [];
        if ( ! empty( $this->lastname ) )
        {
            $name[] = $this->lastname;
        }
        if ( ! empty( $this->firstname ) )
        {
            $name[] = $this->firstname;
        }
        if ( ! empty( $this->middlename ) )
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
        $phones = '';
        if ( ! empty( $this->phone ) )
        {
            $phone = '+7 (' . mb_substr( $this->phone, 0, 3 ) . ') ' . mb_substr( $this->phone, 3, 3 ) . '-' . mb_substr( $this->phone, 6, 2 ) . '-' . mb_substr( $this->phone, 8, 2 );
            if ( $html )
            {
                $phones .= '<a href="tel:7' . $this->phone . '" class="inherit">' . $phone . '</a';
            } else
            {
                $phones .= $phone;
            }
        }
        if ( ! empty( $this->phone2 ) )
        {
            $phone2 = '+7 (' . mb_substr( $this->phone2, 0, 3 ) . ') ' . mb_substr( $this->phone2, 3, 3 ) . '-' . mb_substr( $this->phone2, 6, 2 ) . '-' . mb_substr( $this->phone2, 8, 2 );
            $phones .= '; ';
            if ( $html )
            {
                $phones .= '<a href="tel:7' . $this->phone . '" class="inherit">' . $phone2 . '</a';
            } else
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

    public function getAddressTickets ( $force = false )
    {
        if ( $force || is_null( $this->addressTickets ) )
        {
            $this->addressTickets = Ticket
                ::mine()
                ->where( Ticket::$_table . '.actual_building_id', '=', $this->actual_building_id )
                ->where( Ticket::$_table . '.actual_flat', '=', $this->actual_flat )
                ->get();
        }
        return $this->addressTickets;
    }

    public function getPhoneTickets ( $force = false )
    {
        if ( $force || is_null( $this->phoneTickets ) )
        {
            $this->phoneTickets = $this->tickets()
                ->whereHas( 'managements', function ( $managements )
                {
                    return $managements
                        ->mine();
                } )
                ->get();
        }
        return $this->phoneTickets;
    }

    public function scopeSearch ( $query, $request )
    {
        $customers = self
            ::mine()
            ->orderBy( self::$_table . '.lastname' )
            ->orderBy( self::$_table . '.firstname' )
            ->orderBy( self::$_table . '.middlename' );

        if ( ! empty( $request->get( 'lastname' ) ) )
        {
            $s = '%' . str_replace( ' ', '%', $request->get( 'lastname' ) ) . '%';
            $customers
                ->where( self::$_table . '.lastname', 'like', $s );
        }

        if ( ! empty( $request->get( 'firstname' ) ) )
        {
            $s = '%' . str_replace( ' ', '%', $request->get( 'firstname' ) ) . '%';
            $customers
                ->where( self::$_table . '.firstname', 'like', $s );
        }

        if ( ! empty( $request->get( 'middlename' ) ) )
        {
            $s = '%' . str_replace( ' ', '%', $request->get( 'middlename' ) ) . '%';
            $customers
                ->where( self::$_table . '.middlename', 'like', $s );
        }

        if ( ! empty( $request->get( 'provider_id' ) ) )
        {
            $customers
                ->where( self::$_table . '.provider_id', '=', $request->get( 'provider_id' ) );
        }

        if ( ! empty( $request->get( 'actual_building_id' ) ) )
        {
            $customers
                ->where( self::$_table . '.actual_building_id', '=', $request->get( 'actual_building_id' ) );
        }


        if ( ! empty( $request->get( 'segment_id' ) ) )
        {
            $segmentID = $request->get( 'segment_id' );
            $customers
                ->whereHas( 'actualBuilding', function ( $q ) use ( $segmentID )
                {
                    return $q->where( 'segment_id', $segmentID );
                } );
        }

        if ( ! empty( $request->get( 'actual_flat' ) ) )
        {
            $customers
                ->where( self::$_table . '.actual_flat', '=', $request->get( 'actual_flat' ) );
        }

        if ( ! empty( $request->get( 'phone' ) ) )
        {
            $p = str_replace( '+7', '', $request->get( 'phone' ) );
            $p = preg_replace( '/[^0-9_]/', '', $p );
            $p = '%' . mb_substr( $p, - 10 ) . '%';
            $customers
                ->where( function ( $q ) use ( $p )
                {
                    return $q
                        ->where( self::$_table . '.phone', 'like', $p )
                        ->orWhere( self::$_table . '.phone2', 'like', $p );
                } );
        }

        if ( ! empty( $request->get( 'tags' ) ) )
        {
            $_tags = explode( ',', $request->get( 'tags' ) );
            $customers
                ->whereHas( 'tags', function ( $tags ) use ( $_tags )
                {
                    $i = 0;
                    foreach ( $_tags as $tag )
                    {
                        $tag = trim( $tag );
                        if ( empty( $tag ) ) continue;
                        if ( $i ++ == 0 )
                        {
                            $tags->where( 'text', '=', $tag );
                        } else
                        {
                            $tags->orWhere( 'text', '=', $tag );
                        }
                    }
                    return $tags;
                } );
        }


        return $customers;
    }

}
