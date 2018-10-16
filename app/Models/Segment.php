<?php

namespace App\Models;

use App\User;
use Illuminate\Support\Collection;

class Segment extends BaseModel
{

    protected $table = 'segments';
    public static $_table = 'segments';

    public static $name = 'Сегмент';

    protected $fillable = [
        'name',
        'parent_id',
        'segment_type_id',
    ];

    public function type ()
    {
        return $this->belongsTo('App\Models\SegmentType');
    }

    public function parent ()
    {
        return $this->belongsTo('App\Models\Segment', 'parent_id');
    }

    public function childs ()
    {
        return $this->hasMany('App\Models\Segment', 'parent_id');
    }

    public function buildings ()
    {
        return $this->hasMany('App\Models\Building' );
    }

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

    public function path ()
    {
        return $this->hasOne( 'App\Models\SegmentPath' );
    }

    public function getChildsIds ()
    {
        $childsIds = [ $this->id ];
        return $this->_getChildsIds( $childsIds );
    }

    private function _getChildsIds ( & $childsIds = [] )
    {
        foreach ( $this->childs as $child )
        {
            $childsIds[] = $child->id;
            $child->_getChildsIds( $childsIds );
        }
        return $childsIds;
    }

    public function scopeMine ( $query, User $user = null )
    {
        if ( ! $user ) $user = \Auth::user();
        if ( ! $user ) return false;
        if ( ! Provider::subDomainIs( 'operator', 'system' ) )
        {
            $query
                ->whereIn( self::$_table . '.provider_id', $user->providers()->pluck( Provider::$_table . '.id' ) );
        }
        return $query;
    }

    public function getName ( $withParent = false )
    {
        $name = $this->type->name;
        if ( $withParent && $this->parent )
        {
            $name .= ' ' . $this->parent->name;
        }
        $name .= ' ' . $this->name;
        return $name;
    }

}
