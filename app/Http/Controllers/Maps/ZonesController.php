<?php

namespace App\Http\Controllers\Maps;

use App\Classes\Title;
use App\Models\Geometry;
use App\Models\Management;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class ZonesController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Зоны обслуживания' );
    }

    public function index ( Request $request )
    {
        return view( 'maps.zones.index' );
    }

    public function load ( Request $request )
    {
        $res = Geometry
            ::mine();
        if ( $request->get( 'id' ) )
        {
            $res
                ->where( 'id', '=', $request->get( 'id' ) );
        }
        $res = $res->get();
        $objects = [];
        foreach ( $res as $r )
        {
            $objects[] = [
                'id'                => $r->id,
                'name'              => $r->name,
                'management_id'     => $r->management_id,
                'management_name'   => $r->management->name,
                'type'              => $r->type,
                'coordinates'       => json_decode( $r->coordinates ),
                'fillColor'         => $r->fillColor,
                'strokeColor'       => $r->strokeColor,
                'preset'            => $r->preset,
            ];
        }
        return $objects;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ( Request $request )
    {
        if ( ! \Auth::user()->can( 'maps.zones.edit' ) ) return;
        $managements = Management::mine()->orderBy( 'name' )->pluck( 'name', 'id' );
        if ( $request->get( 'type' ) == 'Polygon' )
        {
            $view = 'maps.zones.polygon';
        }
        else
        {
            $view = 'maps.zones.point';
        }
        return view( $view )
            ->with( 'managements', $managements );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store ( Request $request )
    {

        if ( ! \Auth::user()->can( 'maps.zones.edit' ) )
        {
            if ( $request->ajax() )
            {
                return [
                    'errors' => [ 'Доступ запрещен' ]
                ];
            }
            else
            {
                return redirect()->back()->withErrors( [ 'Доступ запрещен' ] );
            }
        }

        $this->validate( $request, Geometry::$rules );

        $geometry = Geometry::create( $request->all() );
        if ( $geometry instanceof MessageBag )
        {
            if ( $request->ajax() )
            {
                return [
                    'errors' => $geometry
                ];
            }
            else
            {
                return redirect()->back()->withErrors( $geometry );
            }
        }
        $geometry->save();

        if ( $request->ajax() )
        {
            return [
                'success' => 'Объект успешно добавлен',
                'id' => $geometry->id
            ];
        }
        else
        {
            return redirect()->back()->with( 'success', 'Объект успешно добавлен' );
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit ( $id )
    {
        if ( ! \Auth::user()->can( 'maps.zones.edit' ) ) return;
        $object = Geometry::find( $id );
        $managements = Management::mine()->orderBy( 'name' )->pluck( 'name', 'id' );
        if ( $object->type == 'Polygon' )
        {
            $view = 'maps.zones.polygon';
        }
        else
        {
            $view = 'maps.zones.point';
        }
        return view( $view )
            ->with( 'object', $object )
            ->with( 'managements', $managements );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update( Request $request, $id )
    {

        if ( ! \Auth::user()->can( 'maps.zones.edit' ) )
        {
            if ( $request->ajax() )
            {
                return new MessageBag( [ 'Доступ запрещен' ] );
            }
            else
            {
                return redirect()->back()->withErrors( [ 'Доступ запрещен' ] );
            }
        }

        $this->validate( $request, Geometry::$rules );

        $object = Geometry::find( $request->get( 'id', $id ) );
        $res = $object->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            if ( $request->ajax() )
            {
                return $res;
            }
            else
            {
                return redirect()->back()->withErrors( $res );
            }
        }
        if ( $request->ajax() )
        {
            return [
                'success' => 'Объект успешно отредактирован'
            ];
        }
        else
        {
            return redirect()->back()->with( 'success', 'Объект успешно отредактирован' );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy ( $id )
    {
        if ( ! \Auth::user()->can( 'maps.zones.edit' ) ) return;
        $object = Geometry::find( $id );
        $object->delete();
    }

}
