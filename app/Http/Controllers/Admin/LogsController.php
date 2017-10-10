<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Title;
use App\Models\Log;
use Illuminate\Http\Request;

class LogsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Системные логи' );
    }

    public function index ()
    {

        $search = trim( \Input::get( 'search', '' ) );

        $logs = Log
            ::orderBy( 'id', 'desc' );

        if ( !empty( $search ) )
        {
            $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
            $logs
                ->where( 'text', 'like', $s );
        }

        $logs = $logs->paginate( 100 );

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
