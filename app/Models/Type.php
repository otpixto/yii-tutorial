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
        'description',
        'parent_id',
        'mosreg_id',
    ];

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'parent_id',
        'period_acceptance',
        'period_execution',
        'season',
        'is_pay',
        'emergency',
        'need_act',
        'mosreg_id',
    ];

    public function managements ()
    {
        return $this->belongsToMany( 'App\Models\Management', 'managements_types' );
    }

    public function providers ()
    {
        return $this->belongsToMany( 'App\Models\Provider', 'providers_types' );
    }

    public function tickets ()
    {
        return $this->hasMany( 'App\Models\Ticket' );
    }

    public function category ()
    {
        return $this->belongsTo( 'App\Models\Category' );
    }

    public function parent ()
    {
        return $this->belongsTo( 'App\Models\Type' );
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

    public function scopeMine ( $query, ... $flags )
    {
		if ( ! in_array( self::IGNORE_PROVIDER, $flags ) )
		{
			$query
				->whereHas( 'provider', function ( $provider )
				{
					return $provider
						->mine()
						->current();
				});
		}
		return $query;
    }

}
