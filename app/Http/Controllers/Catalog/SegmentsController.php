<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Segments;
use App\Classes\SegmentTree;
use App\Classes\Title;
use App\Models\Building;
use App\Models\BuildingType;
use App\Models\Management;
use App\Models\Provider;
use App\Models\Segment;
use App\Models\SegmentType;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class SegmentsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Сегменты' );
    }

    public function index ( Request $request )
    {


    }

    public function create ()
    {
        Title::add( 'Добавить сегмент' );
        return view( 'catalog.buildings.create' );
    }

    public function store ( Request $request )
    {



    }

    public function show ( $id )
    {
        //
    }

    public function edit ( $id )
    {

        Title::add( 'Редактировать сегмент' );

    }

    public function update ( Request $request, $id )
    {


    }

    public function destroy ( $id )
    {
        //
    }

    public function search ( Request $request )
    {

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';
        $provider_id = $request->get( 'provider_id', Provider::getCurrent() ? Provider::$current->id : null );
        $type_id = $request->get( 'type_id' );

        $res = Segment
            ::mine()
            ->where( 'name', 'like', $s )
            ->orderBy( 'name' );

        if ( ! empty( $provider_id ) )
        {
            $res
                ->where( 'provider_id', '=', $provider_id );
        }

        if ( ! empty( $type_id ) )
        {
            $res
                ->where( 'type_id', '=', $type_id );
        }

        $res = $res
            ->get();

        $segments = [];
        foreach ( $res as $r )
        {
            $segments[] = [
                'id' => $r->id,
                'value' => $r->name,
                'text' => $r->getName()
            ];
        }

        return $segments;

    }

    public function tree ( Request $request )
    {
        $segmentTree = new SegmentTree();
        return $segmentTree->getTree();
    }

    protected function clearCache ()
    {
        Segments::clearCache();
    }

}
