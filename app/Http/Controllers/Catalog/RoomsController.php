<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\BuildingRoom;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class RoomsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Помещения' );
    }

    public function index ( Request $request )
    {



    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store ( Request $request )
    {



    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show ( $id )
    {
        //
    }

    public function info ( $id )
    {

        $room = BuildingRoom::find( $id );

        $customers = Customer
            ::where( 'actual_building_id', '=', $room->building_id )
            ->where( 'actual_flat', '=', $room->number )
            ->get();

        return view( 'catalog.rooms.info' )
            ->with( 'room', $room )
            ->with( 'customers', $customers );

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit ( $id )
    {



    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update ( Request $request, $id )
    {



    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy ( $id )
    {
        //
    }

}
