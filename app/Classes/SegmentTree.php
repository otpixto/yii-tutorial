<?php

namespace App\Classes;

use App\Models\Segment;

class SegmentTree extends Segments
{

    public $tree;

    public function __construct ()
    {
        if ( ! \Cache::tags( self::$cache_tags )->has( 'segments.tree' ) )
        {
            $parentSegments = Segment
                ::mine()
                ->whereNull( 'parent_id' )
                ->orderBy( 'name' )
                ->get();
            $this->tree = [];
            foreach ( $parentSegments as $parentSegment )
            {
                $item = $this->getItem( $parentSegment );
                $nodes = $this->getNodes( $parentSegment );
                if ( $nodes )
                {
                    $item[ 'nodes' ] = $nodes;
                }
                $this->tree[] = $item;
            }
            \Cache::tags( self::$cache_tags )->put( 'segments.tree', $this->tree, self::$cache_life );
        }
        else
        {
            $this->tree = \Cache::tags( self::$cache_tags )->get( 'segments.tree' );
        }
        return $this;
    }

    public function getTree ()
    {
        return $this->tree;
    }

    private function getNodes ( Segment $parentSegment )
    {
        $childs = $parentSegment->childs()->orderBy( 'name' )->get();
        if ( ! $childs->count() ) return null;
        $items = [];
        foreach ( $childs as $child )
        {
            $item = $this->getItem( $child );
            $nodes = $this->getNodes( $child );
            if ( $nodes )
            {
                $item[ 'nodes' ] = $nodes;
            }
            $items[] = $item;
        }
        return $items;
    }

    private function getItem ( Segment $segment )
    {
        $name = $segment->segmentType->name . ' ' . $segment->name;
        if ( $segment->parent )
        {
            $name = $segment->parent->name . ' ' . $name;
        }
        return [
            'id'        => $segment->id,
            'text'      => '<span class="small text-muted">' . $segment->segmentType->name . '</span> ' . $segment->name,
            'name'      => $name
        ];
    }

}