<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Executor;
use Illuminate\Http\Request;

class ExecutorsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Исполнители' );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index ( Request $request )
    {

        $executors = Executor
            ::mine()
            ->with(
                'management'
            )
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        return view( 'catalog.executors.index' )
            ->with( 'executors', $executors );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store ( Request $request )
    {
        //
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit ( $id )
    {
        //
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
        //
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
