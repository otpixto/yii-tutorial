<?php

namespace App\Classes;

use App\Models\Segment;

class SegmentTree extends Segments
{

    public $tree;

    public function __construct ()
    {
        if ( ! \Cache::tags( $this->cache_tags )->has( 'segments.tree' ) )
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
            \Cache::tags( $this->cache_tags )->put( 'segments.tree', $this->tree, $this->cache_life );
        }
        else
        {
            $this->tree = \Cache::tags( $this->cache_tags )->get( 'segments.tree' );
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
        return [
            'id'        => $segment->id,
            'text'      => $segment->name,
            'type'      => $segment->type->name,
        ];
    }

}