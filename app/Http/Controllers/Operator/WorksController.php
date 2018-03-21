<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Models\Address;
use App\Models\Management;
use App\Models\Region;
use App\Models\Type;
use App\Models\Work;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;

class WorksController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Работы на сетях' );
    }

    public function index ( Request $request )
    {

        $works = Work
            ::mine()
            ->orderBy( 'id', 'desc' );

        if ( $request->get( 'show' ) != 'all' )
        {
            $works
                ->current();
        }

        if ( ! empty( $request->get( 'search' ) ) )
        {
            $works
                ->fastSearch( $request->get( 'search' ) );
        }

        if ( ! empty( $request->get( 'id' ) ) )
        {
            $works
                ->where( 'id', '=', $request->get( 'id' ) );
        }

        if ( ! empty( $request->get( 'category_id' ) ) )
        {
            $works
                ->where( 'category_id', '=', $request->get( 'category_id' ) );
        }

        if ( ! empty( $request->get( 'composition' ) ) )
        {
            $q = '%' . str_replace( ' ', '%', $request->get( 'composition' ) ) . '%';
            $works
                ->where( 'composition', 'like', $q );
        }

        if ( ! empty( $request->get( 'reason' ) ) )
        {
            $q = '%' . str_replace( ' ', '%', $request->get( 'reason' ) ) . '%';
            $works
                ->where( 'reason', 'like', $q );
        }

        if ( ! empty( $request->get( 'date' ) ) )
        {
            $dt = Carbon::parse( $request->get( 'date' ) )->toDateString();
            $works
                ->whereRaw( 'DATE( time_begin ) <= ?', [ $dt ] )
                ->whereRaw( 'DATE( time_end_fact ) >= ?', [ $dt ] );
        }

        if ( !empty( $request->get( 'type_id' ) ) )
        {
            $works
                ->where( 'type_id', '=', $request->get( 'type_id' ) );
        }

        if ( !empty( $request->get( 'address_id' ) ) )
        {
            $address_id = $request->get( 'address_id' );
            $works
                ->whereHas( 'addresses', function ( $q ) use ( $address_id )
                {
                    return $q
                        ->where( 'address_id', '=', $address_id );
                });
            $address = Address::find( $address_id );
        }

        if ( !empty( $request->get( 'management_id' ) ) )
        {
            $works
                ->where( 'management_id', '=', $request->get( 'management_id' ) );
        }

        if ( $request->get( 'export' ) == 1 && \Auth::user()->can( 'works.export' ) )
        {
            $works = $works->get();
            $data = [];
            foreach ( $works as $work )
            {
                $data[] = [
                    '#'                             => $work->id,
                    'Дата и время'                  => $work->created_at->format( 'd.m.y H:i' ),
                    'Кто сообщил'                   => $work->who,
                    'Основание'                     => $work->reason,
                    'Адрес работ'                   => $work->addresses->implode( 'name', '; ' ),
                    'Категория работ'               => $work->category->name,
                    'Исполнитель работ'             => $work->management->name,
                    'Состав работ'                  => $work->composition,
                    'Время начала работ'            => Carbon::parse( $work->time_begin )->format( 'd.m.y H:i' ),
                    'Время окончания работ'         => Carbon::parse( $work->time_end )->format( 'd.m.y H:i' ),
                ];
            }
            \Excel::create( 'РАБОТЫ НА СЕТЯХ', function ( $excel ) use ( $data )
            {
                $excel->sheet( 'РАБОТЫ НА СЕТЯХ', function ( $sheet ) use ( $data )
                {
                    $sheet->fromArray( $data );
                });
            })->export( 'xls' );
        }

        $works = $works
            ->paginate( 30 )
            ->appends( $request->all() );

        $regions = Region
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        return view( 'works.index' )
            ->with( 'works', $works )
            ->with( 'managements', Management::orderBy( 'name' )->get() )
            ->with( 'types', Type::orderBy( 'name' )->get() )
            ->with( 'regions', $regions )
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

        $addresses = new Collection();

        if ( !empty( \Input::old( 'address_id', [] ) ) )
        {
            $addresses = Address
                ::whereIn( 'id', \Input::old( 'address_id' ) )
                ->get();
        }

        $regions = Region
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        return view( 'works.create' )
            ->with( 'managements', Management::mine()->orderBy( 'name' )->get() )
            ->with( 'types', Type::orderBy( 'name' )->get() )
            ->with( 'regions', $regions )
            ->with( 'addresses', $addresses );

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

        if ( ! isset( Work::$categories[ $request->get( 'category_id' ) ] ) )
        {
            return redirect()->back()->withErrors( [ 'Некорректная категория' ] );
        }

        \DB::beginTransaction();

        $work = Work::create( $request->all() );

        if ( $work instanceof MessageBag )
        {
            return redirect()->back()->withErrors( $work );
        }

        if ( !empty( $request->comment ) )
        {
            $comment = $work->addComment( $request->comment );
            if ( $comment instanceof MessageBag )
            {
                return redirect()->back()->withErrors( $comment );
            }
        }

        $work->addresses()->sync( $request->get( 'address_id', [] ) );

        \DB::commit();

        return redirect()->route( 'works.edit', $work->id )
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

        if ( \Auth::user()->can( 'works.edit' ) )
        {
            return redirect()->route( 'works.edit', $id );
        }

        Title::add( 'Просмотр сообщения' );

        $work = Work::find( $id );

        if ( ! $work )
        {
            return redirect()->back()->withErrors(['Запись не найдена']);
        }

        return view( 'works.show' )
            ->with( 'work', $work );

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit ( $id )
    {

        if ( ! \Auth::user()->can( 'works.edit' ) )
        {
            return redirect()->route( 'works.show', $id );
        }

        Title::add( 'Редактировать сообщение' );

        $work = Work::find( $id );

        if ( ! $work )
        {
            return redirect()->back()->withErrors( [ 'Запись не найдена' ] );
        }

        $managements = Management::orderBy( 'name' )->get();
        $types = Type::orderBy( 'name' )->get();

        $regions = Region
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        return view( 'works.edit' )
            ->with( 'work', $work )
            ->with( 'managements', $managements )
            ->with( 'regions', $regions )
            ->with( 'types', $types );

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update ( Request $request, $id )
    {

        $this->validate( $request, Work::$rules );

        if ( ! isset( Work::$categories[ $request->get( 'category_id' ) ] ) )
        {
            return redirect()->back()->withErrors( [ 'Некорректная категория' ] );
        }

        $work = Work::find( $id );

        if ( ! $work )
        {
            return redirect()->back()->withErrors( [ 'Запись не найдена' ] );
        }

        \DB::beginTransaction();

        $res = $work->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()->withErrors( $res );
        }

        $work->addresses()->sync( $request->get( 'address_id', [] ) );

        \DB::commit();

        return redirect()->back()
            ->with( 'success', 'Сообщение успешно обновлено' );

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy ( $id )
    {
        //
    }

    public function comment ( Request $request, $id )
    {



    }

    public function search ( Request $request )
    {

        $now = Carbon::now()->toDateString();

        $works = Work
            ::whereHas( 'addresses', function ( $a ) use ( $request )
            {
                return $a
                    ->where( 'address_id', '=', $request->get( 'address_id' ) );
            })
            ->whereRaw( 'DATE( time_begin ) <= ? AND DATE( time_end ) >= ?', [ $now, $now ] )
            ->orderBy( 'id', 'desc' )
            ->take( 10 )
            ->get();

        if ( $works->count() )
        {
            return view( 'works.select' )
                ->with( 'works', $works );
        }

    }

}
