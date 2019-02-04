<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class UserController extends Controller
{

    public function __construct ( Request $request )
    {
        \Debugbar::disable();
        $this->logs = new Logger( 'REST' );
        $this->logs->pushHandler( new StreamHandler( storage_path( 'logs/rest_users.log' ) ) );
        $this->logs->addInfo( 'Запрос от ' . $request->ip(), $request->all() );
    }

    public function addresses ( Request $request )
    {

        $term = trim( $request->get( 'term', '' ) );
        if ( empty( $term ) || mb_strlen( $term ) < 3 )
        {
            return [];
        }

        $term = '%' . str_replace( ' ', '%', $term ) . '%';

        $addresses = Building
            ::where( 'name', 'like', $term )
            ->select(
                'id',
                'name AS text'
            )
            ->orderBy( 'name' )
            ->take( 30 )
            ->get();

        return $addresses;

    }

}