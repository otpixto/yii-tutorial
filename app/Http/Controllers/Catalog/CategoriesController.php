<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Категории обращений' );
    }

    public function index()
    {

        $search = trim( \Input::get( 'search', '' ) );

        $categories = Category
            ::orderBy( 'name' );

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $categories
                ->where( function ( $q ) use ( $s )
                {
                    return $q
                        ->where( 'name', 'like', $s );
                });
        }

        $categories = $categories->paginate( 30 );

        return view( 'catalog.categories.index' )
            ->with( 'categories', $categories );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        Title::add( 'Добавить категорию обращений' );
        return view( 'catalog.categories.create' );
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

        $category = Category::create( $request->all() );
        $category->save();

        return redirect()->route( 'categories.index' )
            ->with( 'success', 'Категория успешно добавлена' );

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

        $category = Category::find( $id );

        return view( 'catalog.categories.edit' )
            ->with( 'category', $category );

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

        $category = Category::find( $id );

        if ( !$category )
        {
            return redirect()->route( 'categories.index' )
                ->withErrors( [ 'Категория не найдена' ] );
        }

        $this->validate( $request, Category::$rules );

        $category->fill( $request->all() );
        $category->save();

        return redirect()->route( 'categories.edit', $category->id )
            ->with( 'success', 'Категория успешно отредактирована' );

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
