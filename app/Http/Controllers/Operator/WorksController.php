<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Models\Address;
use App\Models\Management;
use App\Models\Type;
use App\Models\Work;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WorksController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Работы на сетях' );
    }

    public function index()
    {

        $types = Type::all();
        $managements = Management::all();

        $works = Work
            ::orderBy( 'id', 'desc' );

        if ( !empty( \Input::get( 'search' ) ) )
        {
            $works
                ->fastSearch( \Input::get( 'search' ) );
        }

        if ( !empty( \Input::get( 'id' ) ) )
        {
            $works
                ->where( 'id', '=', \Input::get( 'id' ) );
        }

        if ( !empty( \Input::get( 'date' ) ) )
        {
            $works
                ->whereRaw( 'DATE( time_begin ) <= ?', [ Carbon::parse( \Input::get( 'date' ) )->toDateString() ] )
                ->whereRaw( 'DATE( time_end ) >= ?', [ Carbon::parse( \Input::get( 'date' ) )->toDateString() ] );
        }

        if ( !empty( \Input::get( 'type_id' ) ) )
        {
            $works
                ->where( 'type_id', '=', \Input::get( 'type_id' ) );
        }

        if ( !empty( \Input::get( 'address_id' ) ) )
        {
            $works
                ->where( 'address_id', '=', \Input::get( 'address_id' ) );
            $address = Address::find( \Input::get( 'address_id' ) );
        }

        if ( !empty( \Input::get( 'management_id' ) ) )
        {
            $works
                ->where( 'management_id', '=', \Input::get( 'management_id' ) );
        }

        $works = $works->paginate( 30 );

        return view( 'works.index' )
            ->with( 'works', $works )
            ->with( 'types', $types )
            ->with( 'managements', $managements )
            ->with( 'address', $address ?? null );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {

        Title::add( 'Добавить сообщение' );

        $managements = Management::orderBy( 'name' )->get();
        $types = Type::orderBy( 'name' )->get();

        if ( !empty( \Input::old( 'address_id' ) ) )
        {
            $address = Address::find( \Input::old( 'address_id' ) );
        }

        return view( 'works.create' )
            ->with( 'managements', $managements )
            ->with( 'types', $types )
            ->with( 'address', $address ?? null );

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store ( Request $request )
    {

        $this->validate( $request, Work::$rules );

        $work = Work::create( $request->all() );

        if ( !empty( $request->comment ) )
        {
            $work->addComment( $request->comment );
        }

        return redirect()->route( 'works.index' )
            ->with( 'success', 'Сообщение успешно добавлено' );

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show ( $id )
    {



    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        Title::add( 'Редактировать сообщение' );

        $work = Work::find( $id );

        if ( ! $work )
        {
            return redirect()->back()->withErrors(['Запись не найдена']);
        }

        $managements = Management::orderBy( 'name' )->get();
        $types = Type::orderBy( 'name' )->get();
        $address = Address::find( $work->address_id );

        return view( 'works.edit' )
            ->with( 'work', $work )
            ->with( 'managements', $managements )
            ->with( 'types', $types )
            ->with( 'address', $address );

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $this->validate( $request, Work::$rules );

        $work = Work::find( $id );

        if ( ! $work )
        {
            return redirect()->back()->withErrors(['Запись не найдена']);
        }

        $work->edit( $request->all() );

        return redirect()->route( 'works.index' )
            ->with( 'success', 'Сообщение успешно обновлено' );

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function comment ( Request $request, $id )
    {



    }

}
