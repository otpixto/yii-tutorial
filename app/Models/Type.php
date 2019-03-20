<?php

namespace App\Models;

class Type extends BaseModel
{

    protected $table = 'types';
    public static $_table = 'types';

    public static $name = 'Классификатор';

    protected $nullable = [
        'guid',
        'color',
        'description',
        'parent_id',
        'mosreg_id',
    ];

    protected $fillable = [
        'name',
        'color',
        'description',
        'parent_id',
        'group_id',
        'period_acceptance',
        'period_execution',
        'season',
        'is_pay',
        'emergency',
        'need_act',
        'works',
        'lk',
        'mosreg_id',
    ];

    public function managements ()
    {
        return $this->belongsToMany( Management::class, 'managements_types' );
    }

    public function providers ()
    {
        return $this->belongsToMany( Provider::class, 'providers_types' );
    }

    public function tickets ()
    {
        return $this->hasMany( Ticket::class );
    }

    public function parent ()
    {
        return $this->belongsTo( Type::class );
    }

    public function group ()
    {
        return $this->belongsTo( TypeGroup::class );
    }

    public function childs ()
    {
        return $this->hasMany( Type::class, 'parent_id', 'id' );
    }

    public function scopeMine ( $query, ... $flags )
    {
		if ( ! in_array( self::IGNORE_PROVIDER, $flags ) )
		{
			$query
                ->where( function ( $q )
                {
                    return $q
                        ->whereNull( self::$_table . '.provider_id' )
                        ->orWhereHas( 'provider', function ( $provider )
                        {
                            return $provider
                                ->mine()
                                ->current();
                        });
                });
		}
		return $query;
    }

}
