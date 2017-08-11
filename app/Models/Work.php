<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

class Work extends BaseModel
{

    protected $table = 'works';

    public static $rules = [
        'type_id'           => 'required|integer',
        'address_id'        => 'required|integer',
        'management_id'     => 'required|integer',
        'text'              => 'required|max:255',
        'who'               => 'required|max:255',
        'reason'            => 'required|max:255',
        'datetime_begin'    => 'required|date',
        'datetime_end'      => 'required|date',
    ];

    protected $fillable = [
        'type_id',
        'address_id',
        'management_id',
        'text',
        'who',
        'reason',
        'text',
        'datetime_begin',
        'datetime_end',
    ];

    public function type ()
    {
        return $this->belongsTo( 'App\Models\Type' );
    }

    public function address ()
    {
        return $this->belongsTo( 'App\Models\Address' );
    }

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    public function author ()
    {
        return $this->belongsTo( 'App\User' );
    }

    public static function create ( array $attributes = [] )
    {

        $work = new Work( $attributes );
        $work->author_id = Auth::user()->id;
        $work->save();

        return $work;

    }

}
