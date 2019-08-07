<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Segments;
use App\Classes\SegmentTree;
use App\Classes\Title;
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

        $search = trim( $request->get( 'search', '' ) );
        $provider_id = $request->get( 'provider_id' );
        $segment_type_id = $request->get( 'segment_type_id' );

        $segments = Segment
            ::mine()
            ->orderBy( Segment::$_table . '.name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $segments
                ->where( Segment::$_table . '.name', 'like', $s );
        }

        if ( ! empty( $provider_id ) )
        {
            $segments
                ->where( Segment::$_table . '.provider_id', '=', $provider_id );
        }

        if ( ! empty( $segment_type_id ) )
        {
            $segments
                ->where( Segment::$_table . '.segment_type_id', '=', $segment_type_id );
        }

        $segments = $segments
            ->with(
                'segmentType',
                'parent'
            )
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $segmentTypes = SegmentType
            ::mine()
            ->orderBy( SegmentType::$_table . '.sort' )
            ->orderBy( SegmentType::$_table . '.name' )
            ->get();

        $this->addLog( 'Просмотрел список сегментов (стр.' . $request->get( 'page', 1 ) . ')' );

        return view( 'catalog.segments.index' )
            ->with( 'segments', $segments )
            ->with( 'segmentTypes', $segmentTypes );

    }

    public function create ()
    {
        Title::add( 'Добавить сегмент' );
        $segmentTypes = SegmentType
            ::mine()
            ->orderBy( SegmentType::$_table . '.sort' )
            ->orderBy( SegmentType::$_table . '.name' )
            ->pluck( 'name', 'id' );
        return view( 'catalog.segments.create' )
            ->with( 'segmentTypes', $segmentTypes );
    }

    public function store ( Request $request )
    {

        $rules = [
            'provider_id'           => 'required|integer',
            'parent_id'             => 'nullable|integer',
            'segment_type_id'       => 'required|integer',
            'name'                  => 'required|unique:buildings,name|max:255',
        ];

        $this->validate( $request, $rules );

        $segment = Segment::create( $request->all() );

        if ( $segment instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( $segment );
        }

        $segment->save();

        self::clearCache();

        return redirect()->route( 'segments.edit', $segment->id )
            ->with( 'success', 'Сегмент успешно добавлен' );

    }

    public function show ( $id )
    {
        //
    }

    public function edit ( $id )
    {

        Title::add( 'Редактировать сегмент' );

        $segment = Segment::find( $id );

        if ( ! $segment )
        {
            return redirect()->route( 'segments.index' )
                ->withErrors( [ 'Сегмент не найден' ] );
        }

        $segmentTypes = SegmentType
            ::mine()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        return view( 'catalog.segments.edit' )
            ->with( 'segment', $segment )
            ->with( 'segmentTypes', $segmentTypes );

    }

    public function update ( Request $request, $id )
    {

        $segment = Segment::find( $id );

        if ( ! $segment )
        {
            return redirect()->route( 'segments.index' )
                ->withErrors( [ 'Сегмент не найден' ] );
        }

        $rules = [
            'provider_id'           => 'required|integer',
            'parent_id'             => 'nullable|integer',
            'segment_type_id'       => 'required|integer',
            'name'                  => 'required|unique:buildings,name|max:255',
        ];

        $this->validate( $request, $rules );

        $res = $segment->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( $res );
        }

        self::clearCache();

        return redirect()->route( 'segments.edit', $segment->id )
            ->with( 'success', 'Сегмент успешно отредактирован' );

    }

    public function destroy ( $id )
    {
        //
    }

    public function search ( Request $request )
    {

        $s = '%' . str_replace( ' ', '%', trim( $request->get( 'q' ) ) ) . '%';
        $provider_id = $request->get( 'provider_id', Provider::getCurrent()->id ?? null );
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
                'text' => $r->getName( true )
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
