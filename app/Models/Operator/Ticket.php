<?php

namespace App\Models\Operator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Ticket extends Model
{

    protected $table = 'tickets';

    protected $nullable = [
        'management_id',
        'phone2'
    ];

    public static $rules = [
        'type_id'           => 'required|integer',
        'firstname'         => 'required|string|max:100',
        'middlename'        => 'required|string|max:100',
        'lastname'          => 'required|string|max:100',
        'phone1'            => 'required|string|max:11',
        'phone2'            => 'max:11',
        'text'              => 'required|string|max:255',
        'address'           => 'string|max:255'
    ];

    protected $fillable = [
        'type_id',
        'firstname',
        'middlename',
        'lastname',
        'phone1',
        'phone2',
        'text',
        'management_id',
        'address_id',
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
        $new = new Ticket( $attributes );
        $new->author_id = Auth::user()->id;
        $new->save();
        return $new;
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
