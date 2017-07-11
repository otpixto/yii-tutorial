<?php

namespace App\Http\Controllers\Catalog;

use App\Models\Operator\Category;
use App\Models\Operator\Type;
use Illuminate\Http\Request;

class TypesController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $types = Type::orderBy( 'name' )->get();

        return view( 'catalog.types.index' )
            ->with( 'types', $types );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view( 'catalog.types.create' );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $this->validate( $request, Category::$rules );

        $type = Type::create( $request->all() );

        return redirect()->route( 'types.index' )
            ->with( 'success', 'Тип успешно добавлен' );

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
    public function edit($id)
    {

        $type = Type::find( $id );

        if ( !$type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Тип не найдена' ] );
        }

        return view( 'catalog.types.edit' )
            ->with( 'type', $type );

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

        $type = Category::find( $id );

        if ( !$type )
        {
            return redirect()->route( 'types.index' )
                ->withErrors( [ 'Тип не найдена' ] );
        }

        $this->validate( $request, Type::$rules );

        $type->fill( $request->all() );
        $type->save();

        return redirect()->route( 'types.edit', $type->id )
            ->with( 'success', 'Тип успешно отредактирован' );

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

}
