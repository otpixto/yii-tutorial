<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Log;
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
            ::mine()
			->where( 'created_at', '>=', Carbon::now()->subMonth()->toDateTimeString() )
            ->orderBy( 'id', 'desc' );

        if ( ! empty( $request->get( 'date' ) ) )
        {
            $logs
                ->where( \DB::raw( 'DATE( created_at )' ), '=', Carbon::parse( $request->get( 'date' ) )->toDateString() );
        }

        if ( ! empty( $request->get( 'text' ) ) )
        {
            $logs
                ->whereLike( 'text', $request->get( 'text' ) );
        }

        if ( ! empty( $request->get( 'ip' ) ) )
        {
            $logs
                ->where( 'ip', '=', $request->get( 'ip' ) );
        }

        if ( ! empty( $request->get( 'host' ) ) )
        {
            $logs
                ->where( 'host', '=', $request->get( 'host' ) );
        }

        if ( ! empty( $request->get( 'author_id' ) ) )
        {
            $logs
                ->where( 'author_id', '=', $request->get( 'author_id' ) );
        }

        $logs = $logs
            ->with(
                'author'
            )
            ->paginate( config( 'pagination.per_page' ) )
            ->appends( $request->all() );

        $this->addLog( 'Просмотрел логи (стр.' . $request->get( 'page', 1 ) . ')' );

        return view('admin.logs.index' )
            ->with( 'logs', $logs );

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
