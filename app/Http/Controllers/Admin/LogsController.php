<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Log;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LogsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Системные логи' );
    }

    public function index ( Request $request )
    {

        $logs = Log
            ::orderBy( 'id', 'desc' );

        if ( ! empty( $request->get( 'date' ) ) )
        {
            $logs
                ->where( \DB::raw( 'DATE( created_at )' ), '=', Carbon::parse( $request->get( 'date' ) )->toDateString() );
        }

        if ( ! empty( $request->get( 'text' ) ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $request->get( 'text' ) ) ) . '%';
            $logs
                ->where( 'text', 'like', $s );
        }

        if ( ! empty( $request->get( 'model_name' ) ) )
        {
            $logs
                ->where( 'model_name', '=', $request->get( 'model_name' ) );
        }

        if ( ! empty( $request->get( 'model_id' ) ) )
        {
            $logs
                ->where( 'model_id', '=', $request->get( 'model_id' ) );
        }

        if ( ! empty( $request->get( 'author_id' ) ) )
        {
            $logs
                ->where( 'author_id', '=', $request->get( 'author_id' ) );
        }

        $logs = $logs->paginate( 30 );

        $res = Log
            ::select(
                \DB::raw( 'DISTINCT model_name' ),
                'model_id'
            )
            ->groupBy( 'model_name' )
            ->get();

        $models = [
            null => ' -- ВСЕ -- '
        ];
        foreach ( $res as $r )
        {
            if ( ! $r->parent || ! isset( $r->parent::$name ) ) continue;
            $models[ $r->model_name ] = $r->parent::$name;
        }

        asort( $models );

        $res = User
            ::orderBy( 'lastname' )
            ->orderBy( 'firstname' )
            ->orderBy( 'middlename' )
            ->get();

        $users = [
            null => ' -- ВСЕ -- '
        ];
        foreach ( $res as $r )
        {
            $users[ $r->id ] = $r->getName();
        }

        return view('admin.logs.index' )
            ->with( 'logs', $logs )
            ->with( 'models', $models )
            ->with( 'users', $users );

    }

    public function show ( $id )
    {
        return redirect()->route( 'logs.index' );
    }

    public function edit ( $id )
    {
        return redirect()->route( 'logs.index' );
    }

    public function create ()
    {
        return redirect()->route( 'logs.index' );
    }

    public function update ( Request $request, $id )
    {
        return redirect()->route( 'logs.index' );
    }

    public function store ( Request $request )
    {
        return redirect()->route( 'logs.index' );
    }

}
