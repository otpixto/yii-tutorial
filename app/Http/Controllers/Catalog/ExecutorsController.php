<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Executor;
use App\Models\Management;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

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

        $availableManagements = Management
            ::mine()
            ->orderBy( 'name' )
            ->get();

        $res = [];
        foreach ( $availableManagements as $availableManagement )
        {
            $res[ $availableManagement->parent->name ?? '' ][ $availableManagement->id ] = $availableManagement->name;
        }

        ksort( $res );
        $availableManagements = $res;

        return view( 'catalog.executors.create' )
            ->with( 'availableManagements', $availableManagements );

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store ( Request $request )
    {

        $rules = [
            'management_id'         => 'required|integer',
            'name'                  => 'required|max:191',
            'phone'                 => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        ];

        $this->validate( $request, $rules );

        $executor = Executor::create( $request->all() );
        if ( $executor instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( $executor );
        }

        $executor->save();

        return redirect()->route( 'executors.edit', $executor->id )
            ->with( 'success', 'Исполнитель успешно создан' );

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

        $executor = Executor::find( $id );

        if ( ! $executor )
        {
            return redirect()->route( 'executors.index' )
                ->withErrors( [ 'Исполнитель не найден' ] );
        }

        $availableManagements = Management
            ::mine()
            ->orderBy( 'name' )
            ->get();

        $res = [];
        foreach ( $availableManagements as $availableManagement )
        {
            $res[ $availableManagement->parent->name ?? '' ][ $availableManagement->id ] = $availableManagement->name;
        }

        ksort( $res );
        $availableManagements = $res;

        return view( 'catalog.executors.edit' )
            ->with( 'executor', $executor )
            ->with( 'availableManagements', $availableManagements );

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

        $executor = Executor::find( $id );

        if ( ! $executor )
        {
            return redirect()->back()
                ->withErrors( [ 'Исполнитель не найден' ] );
        }

        $rules = [
            'management_id'         => 'required|integer',
            'name'                  => 'required|max:191',
            'phone'                 => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        ];

        $this->validate( $request, $rules );

        $res = $executor->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( $res );
        }

        return redirect()->route( 'executors.edit', $executor->id )
            ->with( 'success', 'Исполнитель успешно отредактирован' );

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
