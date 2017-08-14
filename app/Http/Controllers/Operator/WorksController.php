<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\BaseModel;
use App\Models\Management;
use App\Models\Type;
use App\Models\Work;
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

        $works = Work::orderBy( 'id', 'desc' )->paginate( 30 );

        return view( 'works.index' )
            ->with( 'works', $works );

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
