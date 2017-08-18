<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Work extends BaseModel
{

    protected $table = 'works';

    public static $rules = [
        'type_id'           => 'required|integer',
        'address_id'        => 'required|integer',
        'management_id'     => 'required|integer',
        'comment'           => 'max:255',
        'who'               => 'required|max:255',
        'reason'            => 'required|max:255',
        'date_begin'        => 'required|date_format:d.m.Y',
        'time_begin'        => 'required|date_format:G:i',
        'date_end'          => 'required|date_format:d.m.Y',
        'time_end'          => 'required|date_format:G:i',
    ];

    protected $fillable = [
        'type_id',
        'address_id',
        'management_id',
        'who',
        'reason',
        'text',
        'composition',
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

        $exp = explode( ':', $attributes['time_begin'] );
        $dt_begin = Carbon::parse( $attributes['date_begin'] )->setTime( $exp[0], $exp[1] );

        $exp = explode( ':', $attributes['time_end'] );
        $dt_end = Carbon::parse( $attributes['date_end'] )->setTime( $exp[0], $exp[1] );

        $work = new Work( $attributes );
        $work->author_id = Auth::user()->id;
        $work->time_begin = $dt_begin->toDateTimeString();
        $work->time_end = $dt_end->toDateTimeString();
        $work->save();

        return $work;

    }

    public function edit ( array $attributes = [] )
    {

        $exp = explode( ':', $attributes['time_begin'] );
        $dt_begin = Carbon::parse( $attributes['date_begin'] )->setTime( $exp[0], $exp[1] );

        $exp = explode( ':', $attributes['time_end'] );
        $dt_end = Carbon::parse( $attributes['date_end'] )->setTime( $exp[0], $exp[1] );

        $this->fill( $attributes );
        $this->time_begin = $dt_begin->toDateTimeString();
        $this->time_end = $dt_end->toDateTimeString();
        $this->save();

        return $this;

    }

    public function scopeFastSearch ( $query, $search )
    {
        $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
        return $query
            ->where( function ( $q ) use ( $s )
            {
                return $q
                    ->where( 'reason', 'like', $s )
                    ->orWhere( 'who', 'like', $s )
                    ->orWhere( 'composition', 'like', $s )
                    ->orWhereHas( 'address', function ( $q2 ) use ( $s )
                    {
                        return $q2->where( 'name', 'like', $s );
                    })
                    ->orWhereHas( 'management', function ( $q2 ) use ( $s )
                    {
                        return $q2->where( 'name', 'like', $s );
                    })
                    ->orWhereHas( 'type', function ( $q2 ) use ( $s )
                    {
                        return $q2->where( 'name', 'like', $s );
                    });
            });
    }

    public function getClass ()
    {
        $dt_now = Carbon::now();
        $dt_begin = Carbon::parse( $this->time_begin );
        $dt_end = Carbon::parse( $this->time_end );
        if ( $dt_begin->timestamp <= $dt_now->timestamp && $dt_now->timestamp <= $dt_end->timestamp )
        {
            return 'warning';
        }
        return 'text-muted';
    }

}
