<?php

namespace App\Models\Operator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Ticket extends Model
{

    protected $table = 'tickets';

    protected $nullable = [
        'management_id',
        'address_id',
        'customer_id'
    ];

    public static $rules = [
        'type_id'           => 'required|integer',
        'firstname'         => 'required|max:191',
        'middlename'        => 'nullable|max:191',
        'lastname'          => 'nullable|max:191',
        'phone'             => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        'phone2'            => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        'text'              => 'required|max:191',
        'address'           => 'max:191'
    ];

    protected $fillable = [
        'type_id',
        'firstname',
        'middlename',
        'lastname',
        'phone',
        'phone2',
        'text',
        'address'
    ];

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Operator\Management' );
    }

    public function address ()
    {
        return $this->belongsTo( 'App\Models\Operator\Address' );
    }

    public function author ()
    {
        return $this->belongsTo( 'App\User' );
    }

    public function type ()
    {
        return $this->belongsTo( 'App\Models\Operator\Type' );
    }

    public function scopeMine ( $query )
    {
        return $query
            ->where( function ( $q )
            {
                return $q
                    ->where( 'author_id', '=', Auth::user()->id );
            });
    }

    public static function create ( array $attributes = [] )
    {

        $attributes['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes['phone'] ), -10 );
        if ( !empty( $attributes['phone2'] ) )
        {
            $attributes['phone2'] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes['phone2'] ), -10 );
        }

        $ticket = new Ticket( $attributes );
        $ticket->author_id = Auth::user()->id;

        $address = Address
            ::where( 'name', '=', trim( $ticket->address ) )
            ->first();
        if ( $address )
        {
            $ticket->address_id = $address->id;
            $ticket->management_id = $address->management_id;
        }

        $ticket->save();
        return $ticket;

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

}
