<?php

namespace App\Http\Controllers\Catalog;

use App\Classes\Title;
use App\Models\Executor;
use App\Models\Log;
use App\Models\Management;
use App\User;
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

        $search = trim( $request->get( 'search', '' ) );

        $executors = Executor
            ::mine()
            ->with(
                'management',
                'management.parent',
                'tickets',
                'works'
            );

        if ( ! empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', $search ) . '%';
            $executors
                ->where( Executor::$_table . '.name', 'like', $s )
                ->orWhere( Executor::$_table . '.phone', 'like', mb_substr( preg_replace( '/\D/', '', $search ), -10 ) );
        }

        $executors = $executors
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $this->addLog( 'Просмотрел список исполнителей (стр.' . $request->get( 'page', 1 ) . ')' );

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

    public function user ( Request $request, $id )
    {

        $rules = [
            'user_id'         => 'nullable|integer',
        ];

        $this->validate( $request, $rules );

        $executor = Executor::mine()->find( $id );
        if ( $executor instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( [ 'Исполнитель не найден' ] );
        }

        if ( $request->get( 'user_id' ) )
        {
            $user = User::mine()->find( $request->get( 'user_id' ) );
            if ( ! $user )
            {
                return redirect()->back()
                    ->withInput()
                    ->withErrors( [ 'Пользователь не найден' ] );
            }
            $executor->user_id = $user->id;
            $executor->save();
        }
        else
        {
            $executor->user_id = null;
            $executor->save();
        }

        return redirect()->back()
            ->with( 'success', 'Исполнителю успешно назначен пользователь' );

    }

}
