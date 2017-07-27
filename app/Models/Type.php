<?php

namespace App\Models;

use Illuminate\Validation\Rule;

class Type extends BaseModel
{

    protected $table = 'types';

    protected $fillable = [
        'name',
        'category_id',
        'period_acceptance',
        'period_execution',
        'season',
    ];

    public function tickets ()
    {
        return $this->hasMany( 'App\Models\Ticket' );
    }

    public function category ()
    {
        return $this->belongsTo( 'App\Models\Category' );
    }

    public static function create ( array $attributes = [] )
    {
        $new = new Type( $attributes );
        $new->need_act = isset( $attributes['need_act'] ) ? 1 : 0;
        $new->save();
        return $new;
    }

    public static function getRules ( $ignore = null )
    {
        $unique = Rule::unique( 'types' );
        if ( $ignore )
        {
            $unique->ignore( $ignore, 'id' );
        }
        return [
            'name' => [
                'required',
                'max:255',
                $unique
            ],
            'category_id' => 'required|integer',
            'period_acceptance' => 'numeric',
            'period_execution'  => 'numeric',
            'need_act'          => 'boolean',
        ];
    }

}
