<?php

namespace App\Classes;

use App\Models\Segment;

class SegmentChilds extends Segments
{

    public $ids = [];

    public function __construct ( Segment $segment = null )
    {
        if ( ! \Cache::tags( $this->cache_tags )->has( 'segments.childs.' . $segment->id ) )
        {
            $this->ids[] = $segment->id;
            $this->getChildsIds( $segment );
            \Cache::tags( $this->cache_tags )->put( 'segments.childs.' . $segment->id, $this->ids, $this->cache_life );
        }
        else
        {
            $this->ids = \Cache::tags( $this->cache_tags )->get( 'segments.childs.' . $segment->id );
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