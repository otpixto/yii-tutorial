<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Models\Building;
use App\Models\Executor;
use App\Models\Management;
use App\Models\Provider;
use App\Models\Segment;
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
        Title::add( 'Отключения' );
    }

    public function index ( Request $request )
    {

        $managements = [];

        if ( ! empty( $request->get( 'managements' ) ) )
        {
            $managements = explode( ',', $request->get( 'managements' ) );
        }

        $works = Work
            ::mine()
            ->orderBy( Work::$_table . '.id', 'desc' );

        if ( $request->get( 'show' ) != 'all' )
        {
            $works
                ->current();
        }

        switch ( $request->get( 'show' ) )
        {
            case 'all':

                break;
            case 'overdue':
                $works
                    ->whereRaw( 'time_end < COALESCE( time_end_fact, CURRENT_TIMESTAMP )' );
                break;
            default:
                $works
                    ->current();
                break;
        }

        if ( ! empty( $request->get( 'id' ) ) )
        {
            $works
                ->where( Work::$_table . '.id', '=', $request->get( 'id' ) );
        }

        if ( ! empty( $request->get( 'category_id' ) ) )
        {
            $works
                ->where( Work::$_table . '.category_id', '=', $request->get( 'category_id' ) );
        }

        if ( ! empty( $request->get( 'composition' ) ) )
        {
            $q = '%' . str_replace( ' ', '%', $request->get( 'composition' ) ) . '%';
            $works
                ->where( Work::$_table . '.composition', 'like', $q );
        }

        if ( ! empty( $request->get( 'reason' ) ) )
        {
            $q = '%' . str_replace( ' ', '%', $request->get( 'reason' ) ) . '%';
            $works
                ->where( Work::$_table . '.reason', 'like', $q );
        }

        if ( ! empty( $request->get( 'begin_from' ) ) )
        {
            $works
                ->where( Work::$_table . '.time_begin', '>=', Carbon::parse( $request->get( 'begin_from' ) )
                    ->toDateTimeString() );
        }

        if ( ! empty( $request->get( 'begin_to' ) ) )
        {
            $works
                ->where( Work::$_table . '.time_begin', '<=', Carbon::parse( $request->get( 'begin_to' ) )
                    ->toDateTimeString() );
        }

        if ( ! empty( $request->get( 'end_from' ) ) )
        {
            $works
                ->where( function ( $q ) use ( $request )
                {
                    return $q
                        ->where( Work::$_table . '.time_end', '>=', Carbon::parse( $request->get( 'end_from' ) )
                            ->toDateTimeString() )
                        ->orWhere( Work::$_table . '.time_end_fact', '>=', Carbon::parse( $request->get( 'end_from' ) )
                            ->toDateTimeString() );
                } );
        }

        if ( ! empty( $request->get( 'end_to' ) ) )
        {
            $works
                ->where( function ( $q ) use ( $request )
                {
                    return $q
                        ->where( Work::$_table . '.time_end', '<=', Carbon::parse( $request->get( 'end_to' ) )
                            ->toDateTimeString() )
                        ->orWhere( Work::$_table . '.time_end_fact', '<=', Carbon::parse( $request->get( 'end_to' ) )
                            ->toDateTimeString() );
                } );
        }

        if ( ! empty( $request->get( 'type_id' ) ) )
        {
            $works
                ->where( Work::$_table . '.type_id', '=', $request->get( 'type_id' ) );
        }

        if ( ! empty( $request->get( 'executor_id' ) ) )
        {
            $works
                ->where( Work::$_table . '.executor_id', '=', $request->get( 'executor_id' ) );
        }

        if ( ! empty( $request->get( 'building_id' ) ) )
        {
            $works
                ->whereHas( 'buildings', function ( $buildings ) use ( $request )
                {
                    return $buildings
                        ->where( Building::$_table . '.id', '=', $request->get( 'building_id' ) );
                } );
            $building = Building::where( 'id', '=', $request->get( 'building_id' ) )
                ->pluck( 'name', 'id' );
        }

        if ( ! empty( $request->get( 'segment_id' ) ) )
        {
            $segment = Segment::find( $request->get( 'segment_id' ) );
            $works
                ->whereHas( 'buildings', function ( $buildings ) use ( $request )
                {
                    return $buildings
                        ->where( Building::$_table . '.segment_id', '=', $request->get( 'segment_id' ) );
                } );
        }

        if ( count( $managements ) )
        {
            $works
                ->whereIn( Work::$_table . '.management_id', $managements );
        }

        if ( $request->get( 'export' ) == 1 && \Auth::user()
                ->can( 'works.export' ) )
        {
            $works = $works->get();
            $data = [];
            foreach ( $works as $work )
            {
                $data[] = [
                    '#' => $work->id,
                    'Дата и время' => $work->created_at->format( 'd.m.y H:i' ),
                    'Кто сообщил' => $work->who,
                    'Основание' => $work->reason,
                    'Адрес работ' => $work->buildings->implode( 'name', '; ' ),
                    'Категория работ' => $work->category->name,
                    'Исполнитель работ' => $work->management->name,
                    'Состав работ' => $work->composition,
                    'Время начала работ' => Carbon::parse( $work->time_begin )
                        ->format( 'd.m.y H:i' ),
                    'Время окончания работ' => Carbon::parse( $work->time_end )
                        ->format( 'd.m.y H:i' ),
                ];
            }
            \Excel::create( 'Отключения', function ( $excel ) use ( $data )
            {
                $excel->sheet( 'Отключения', function ( $sheet ) use ( $data )
                {
                    $sheet->fromArray( $data );
                } );
            } )
                ->export( 'xls' );
        }

        $works = $works
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( Provider::$_table . '.name' )
            ->pluck( Provider::$_table . '.name', Provider::$_table . '.id' );

        $res = Management
            ::mine()
            ->whereHas( 'parent' )
            ->with( 'parent' )
            ->get()
            ->sortBy( 'name' );
        $availableManagements = [];
        foreach ( $res as $r )
        {
            $availableManagements[ $r->parent->name ][ $r->id ] = $r->name;
        }

        return view( 'works.index' )
            ->with( 'works', $works )
            ->with( 'availableManagements', $availableManagements )
            ->with( 'managements', $managements )
            ->with( 'providers', $providers )
            ->with( 'building', $building ?? [] )
            ->with( 'segment', $segment ?? [] );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {

        Title::add( 'Добавить сообщение' );

        $buildings = new Collection();

        if ( ! empty( \Input::old( 'building_id', [] ) ) )
        {
            $buildings = Building
                ::whereIn( 'id', \Input::old( 'building_id' ) )
                ->get();
        }

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        return view( 'works.create' )
            ->with( 'managements', Management::mine()->orderBy( 'name' )->get() )
            ->with( 'types', Type::orderBy( 'name' )->get() )
            ->with( 'providers', $providers )
            ->with( 'buildings', $buildings );

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
            return redirect()
                ->back()
                ->withErrors( [ 'Некорректная категория' ] );
        }

        \DB::beginTransaction();

        $work = Work::create( $request->all() );

        if ( $work instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $work );
        }

        if ( ! empty( $request->get( 'executor_name' ) ) )
        {
            $executor = Executor::create([
                'management_id'     => $work->management_id,
                'name'              => $request->get( 'executor_name' ),
                'phone'             => $request->get( 'executor_phone' ),
            ]);
            if ( $executor instanceof MessageBag )
            {
                return redirect()
                    ->back()
                    ->withErrors( $executor );
            }
            $executor->save();
            $work->executor_id = $executor->id;
            $work->save();
        }

        if ( ! empty( $request->comment ) )
        {
            $comment = $work->addComment( $request->comment );
            if ( $comment instanceof MessageBag )
            {
                return redirect()
                    ->back()
                    ->withErrors( $comment );
            }
        }

        $work->buildings()
            ->sync( $request->get( 'buildings', [] ) );

        \DB::commit();

        \Cache::tags( 'works_counts' )
            ->flush();

        return redirect()
            ->route( 'works.edit', $work->id )
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
            return redirect()->route( 'works.index' )->withErrors( [ 'Запись не найдена' ] );
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
            return redirect()->route( 'works.index' )->withErrors( [ 'Запись не найдена' ] );
        }

        $managements = Management::orderBy( 'name' )->get();

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( Provider::$_table . '.name' )
            ->pluck( Provider::$_table . '.name', Provider::$_table . '.id' );

        return view( 'works.edit' )
            ->with( 'work', $work )
            ->with( 'managements', $managements )
            ->with( 'providers', $providers );

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
            return redirect()
                ->back()
                ->withErrors( [ 'Некорректная категория' ] );
        }

        $work = Work::find( $id );

        if ( ! $work )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Запись не найдена' ] );
        }

        \DB::beginTransaction();

        $res = $work->edit( $request->all() );
        if ( $res instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $res );
        }

        if ( ! empty( $request->get( 'executor_name' ) ) )
        {
            $executor = Executor::create([
                'management_id'     => $work->management_id,
                'name'              => $request->get( 'executor_name' ),
                'phone'             => $request->get( 'executor_phone' ),
            ]);
            if ( $executor instanceof MessageBag )
            {
                return redirect()
                    ->back()
                    ->withErrors( $executor );
            }
            $executor->save();
            $work->executor_id = $executor->id;
            $work->save();
        }

        $work->buildings()
            ->sync( $request->get( 'buildings', [] ) );

        \DB::commit();

        \Cache::tags( 'works_counts' )
            ->flush();

        return redirect()
            ->back()
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

    public function filter ( Request $request )
    {

        $data = $request->all();

        unset( $data[ '_token' ] );

        foreach ( $data as $key => $val )
        {
            if ( empty( $val ) )
            {
                unset( $data[ $key ] );
            }
        }

        if ( isset( $data[ 'managements' ] ) )
        {
            $data[ 'managements' ] = implode( ',', $data[ 'managements' ] );
        }

        $url = route( 'works.index', $data ) . '#result';

        return redirect()->to( $url );

    }

    public function search ( Request $request )
    {

        $now = Carbon::now()->toDateString();

        $works = Work
            ::whereHas( 'buildings', function ( $buildings ) use ( $request )
            {
                return $buildings
                    ->where( Building::$_table. '.id', '=', $request->get( 'building_id' ) );
            })
            ->whereRaw( 'DATE( time_begin ) <= ? AND DATE( time_end ) >= ?', [ $now, $now ] )
            ->orderBy( Work::$_table . '.id', 'desc' )
            ->take( 10 )
            ->get();

        if ( $works->count() )
        {
            return view( 'works.select' )
                ->with( 'works', $works );
        }

    }

}
