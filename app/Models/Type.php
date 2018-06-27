<?php

namespace App\Models;

use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rule;

class Type extends BaseModel
{

    protected $table = 'types';
    public static $_table = 'types';

    public static $name = 'Классификатор';

    protected $nullable = [
        'guid',
    ];

    protected $fillable = [
        'name',
        'category_id',
        'period_acceptance',
        'period_execution',
        'season',
        'is_pay',
        'emergency',
        'need_act',
    ];

    public function addresses ()
    {
        return $this->belongsToMany( 'App\Models\Address', 'addresses_types' );
    }

    public function managements ()
    {
        return $this->belongsToMany( 'App\Models\Management', 'managements_types' );
    }

    public function tickets ()
    {
        return $this->hasMany( 'App\Models\Ticket' );
    }

    public function category ()
    {
        return $this->belongsTo( 'App\Models\Category' );
    }

    public function edit ( array $attributes = [] )
    {
        $res = $this->saveLogs( $attributes );
        if ( $res instanceof MessageBag )
        {
            return $res;
        }
        $this->fill( $attributes );
        if ( isset( $attributes[ 'checkboxes' ] ) )
        {
            if ( empty( $attributes['need_act'] ) && $this->need_act == 1 )
            {
                $this->need_act = 0;
                $this->saveLog( 'need_act', 1, 0 );
            }
            if ( empty( $attributes['is_pay'] ) && $this->is_pay == 1 )
            {
                $this->is_pay = 0;
                $this->saveLog( 'is_pay', 1, 0 );
            }
            if ( empty( $attributes['emergency'] ) && $this->emergency == 1 )
            {
                $this->emergency = 0;
                $this->saveLog( 'emergency', 1, 0 );
            }
        }
        $this->save();
        return $this;
    }

}
