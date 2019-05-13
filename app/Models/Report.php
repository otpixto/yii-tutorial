<?php

namespace App\Models;

use Carbon\Carbon;

class Report extends BaseModel
{

    protected $fillable = [
        'date_from',
        'date_to',
        'data'
    ];

    protected $nullable = [
        'date_from',
        'date_to',
        'data'
    ];

    protected $dates = [
        'date_from',
        'date_to',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public static function create ( array $attributes = [] ) : Report
    {
        $attributes[ 'date_from' ] = Carbon::parse( $attributes[ 'date_from' ] )->toDateTimeString();
        $attributes[ 'date_to' ] = Carbon::parse( $attributes[ 'date_to' ] )->toDateTimeString();
        $new = parent::create( $attributes );
        if ( ! empty( $attributes[ 'data' ] ) )
        {
            $new->setData( $attributes[ 'data' ] );
        }
        return $new;
    }

    public function setData ( array $data = [] )
    {
        $this->data = json_encode( $data );
    }

    public function getData ()
    {
        return $this->data ? json_decode( $this->data, true ) : null;
    }

}