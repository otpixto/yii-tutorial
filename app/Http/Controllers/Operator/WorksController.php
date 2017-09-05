<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Models\Address;
use App\Models\Management;
use App\Models\Type;
use App\Models\Work;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WorksController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Работы на сетях' );
    }

    public function index()
    {

        $works = Work
            ::mine()
            ->orderBy( 'id', 'desc' );

        if ( \Input::get( 'show' ) != 'all' )
        {
            $now = Carbon::now()->toDateString();
            $works
                ->whereRaw( 'DATE( time_begin ) <= ? AND DATE( time_end ) >= ?', [ $now, $now ] );
        }

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

        if ( \Input::get( 'export' ) == 1 )
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
                    'Адрес работ'                   => $work->address->name,
                    'Тип работ'                     => $work->type->name,
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

        $works = $works->paginate( 30 );

        return view( 'works.index' )
            ->with( 'works', $works )
            ->with( 'managements', Management::orderBy( 'name' )->get() )
            ->with( 'types', Type::orderBy( 'name' )->get() )
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

        return view( 'works.create' )
            ->with( 'managements', Management::orderBy( 'name' )->get() )
            ->with( 'types', Type::orderBy( 'name' )->get() )
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

        $work = Work::create( $request->all() );

        if ( !empty( $request->comment ) )
        {
            $work->addComment( $request->comment );
        }

        $work->addresses()->sync( $request->get( 'address_id', [] ) );

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

    public function search ( Request $request )
    {

        $now = Carbon::now()->toDateString();

        $works = Work
            ::whereHas( 'addresses', function ( $a ) use ( $request )
            {
                return $a
                    ->where( 'address_id', '=', $request->get( 'address_id' ) );
            })
            ->where( 'type_id', '=', $request->get( 'type_id' ) )
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
