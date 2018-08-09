<?php

namespace App\Classes;

use App\Models\Segment;

class SegmentChilds extends Segments
{

    public $ids = [];

    public function __construct ( Segment $segment = null )
    {
        if ( ! \Cache::tags( self::$cache_tags )->has( 'segments.childs.' . $segment->id ) )
        {
            $this->ids[] = $segment->id;
            $this->getChildsIds( $segment );
            \Cache::tags( self::$cache_tags )->put( 'segments.childs.' . $segment->id, $this->ids, self::$cache_life );
        }
        else
        {
            $this->ids = \Cache::tags( self::$cache_tags )->get( 'segments.childs.' . $segment->id );
        }
        return $this;
    }

    private function getChildsIds ( Segment $segment )
    {
        foreach ( $segment->childs as $child )
        {
            $this->ids[] = $child->id;
            $this->getChildsIds( $child );
        }
    }

}