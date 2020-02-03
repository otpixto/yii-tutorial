<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

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
        'group_id',
        'mosreg_id',
    ];

    protected $fillable = [
        'provider_id',
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

    public function groups ()
    {
        return $this->belongsToMany( TypeGroup::class, 'group_type', 'type_id', 'group_id' );
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
                        } );
                } );
        }
        if ( !Auth::user()
            ->can( 'tickets.all_types' ) )
        {
            $query
                ->whereHas( 'managements', function ( $managements )
                {
                    $managements->whereHas( 'users', function ( $users )
                    {
                        return $users->where('user_id', Auth::user()->id);
                    });
                    return $managements->mine();
                } );
        }
        return $query;
    }

    public function sortByUsersFavoriteTypes ( array $types ) : array
    {
        $user = auth()->user();

        $favoriteTypesList = $user->favorite_types_list;

        if ( $favoriteTypesList )
        {
            $favoriteTypesArray = json_decode( $favoriteTypesList );

            $typesArray = [];

            foreach ( $favoriteTypesArray as $item )
            {
                if ( array_key_exists( $item, $types ) )
                {
                    if ( isset( $types[ $item ] ) )
                    {
                        $typesArray[ $item ] = $types[ $item ];
                        unset( $types[ $item ] );
                    }
                }
            }

            $result = [];

            foreach ( $typesArray as $key => $value )
            {
                $result[ $key ] = $value;
            }

            foreach ( $types as $key => $value )
            {
                $result[ $key ] = $value;
            }

            $types = $result;
        }
        return $types;
    }

}
