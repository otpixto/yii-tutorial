<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class CategoriesController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Категории обращений' );
    }

    public function index ( Request $request )
    {

        $search = trim( $request->get( 'search', '' ) );

        $categories = Category
            ::orderBy( 'name' );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $categories
                ->where( function ( $q ) use ( $s )
                {
                    return $q
                        ->where( 'name', 'like', $s );
                } );
        }

        $categories = $categories
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        return view( 'catalog.categories.index' )
            ->with( 'categories', $categories );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {
        Title::add( 'Добавить категорию обращений' );
        return view( 'catalog.categories.create' );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store ( Request $request )
    {

        $this->validate( $request, Category::$rules );

        $category = Category::create( $request->all() );
        if ( $category instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $category );
        }
        $category->save();

        self::clearCache();

        return redirect()
            ->route( 'categories.index' )
            ->with( 'success', 'Категория успешно добавлена' );

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

        $category = Category::find( $id );

        return view( 'catalog.categories.edit' )
            ->with( 'category', $category );

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

        $category = Category::find( $id );

        if ( ! $category )
        {
            return redirect()
                ->route( 'categories.index' )
                ->withErrors( [ 'Категория не найдена' ] );
        }

        $this->validate( $request, Category::$rules );
        $attributes = $request->all();
        $attributes[ 'need_act' ] = $request->get( 'need_act', 0 );
        $attributes[ 'emergency' ] = $request->get( 'emergency', 0 );
        $attributes[ 'is_pay' ] = $request->get( 'is_pay', 0 );
        $attributes[ 'works' ] = $request->get( 'works', 0 );

        $res = $category->edit( $attributes );
        if ( $res instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $res );
        }

        self::clearCache();

        return redirect()
            ->route( 'categories.edit', $category->id )
            ->with( 'success', 'Категория успешно отредактирована' );

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
