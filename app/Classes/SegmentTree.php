<?php

namespace App\Classes;

use App\Models\Segment;

class SegmentTree
{

    private $segmentsTree;

    public function __construct ()
    {
        if ( ! \Cache::has( 'segments_tree' ) )
        {
            $parentSegments = Segment
                ::mine()
                ->whereNull( 'parent_id' )
                ->orderBy( 'name' )
                ->get();
            $this->segmentsTree = [];
            foreach ( $parentSegments as $parentSegment )
            {
                $item = $this->getItem( $parentSegment );
                $nodes = $this->getNodes( $parentSegment );
                if ( $nodes )
                {
                    $item[ 'nodes' ] = $nodes;
                }
                $this->segmentsTree[] = $item;
            }
            \Cache::put( 'segments_tree', $this->segmentsTree, 60 );
        }
        else
        {
            $this->segmentsTree = \Cache::get( 'segments_tree' );
        }
    }

    public function getTree ()
    {
        return $this->segmentsTree;
    }

    public function getChildsIds ( $parent_id = null, array & $ids = [] )
    {
        foreach ( $this->segmentsTree as $item )
        {
            $current = $item[ 'nodes' ];
            foreach ( $item[ 'nodes' ] as $node )
            {
                if ( $item[ 'id' ] == $parent_id )
                {
                    $ids[] = $node[ 'id' ];
                }
                $this->getChildsIds( $node[ 'id' ], $ids );
            }
        }
    }

    private function getNodes ( \App\Models\Segment $parentSegment )
    {
        if ( ! $parentSegment->childs->count() ) return null;
        $items = [];
        foreach ( $parentSegment->childs as $child )
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

    private function getItem ( \App\Models\Segment $segment )
    {
        return [
            'id'        => $segment->id,
            'text'      => $segment->name,
            'type'      => $segment->type->name,
        ];
    }

}