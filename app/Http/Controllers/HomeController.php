<?php

namespace App\Http\Controllers;

use App\Classes\Title;

class HomeController extends Controller
{

    public function __construct ()
    {
        $this->middleware('auth' );
        Title::add( 'Главная' );
    }


    public function index ()
    {
		
		/*
		$types = Type::all();
		foreach ( $types as $type )
		{
			list ( $code, $name ) = explode( ' ', $type->name );
			$exp = explode( '.', $code );
			if ( mb_strlen( $exp[ 0 ] ) < 2 )
			{
				$exp[ 0 ] = '0' . $exp[ 0 ];
			}
			if ( ! empty( $exp[ 1 ] ) && mb_strlen( $exp[ 1 ] ) < 2 )
			{
				$exp[ 1 ] = '0' . $exp[ 1 ];
			}
			if ( ! empty( $exp[ 2 ] ) && mb_strlen( $exp[ 2 ] ) < 2 )
			{
				$exp[ 2 ] = '0' . $exp[ 2 ];
			}
			$code = implode( '.', $exp );
			$type->name = $code . ' ' . $name;
			$type->save();
		}
		
		die;
		*/

        return redirect()->route( 'tickets.index' );

    }

    public function about ()
    {

        return view('home' )
            ->with( 'title', 'О компании' );

    }

    public function blank ()
    {
        return view('blank' );
    }

    public function getFile ()
    {

        return view('home' )
            ->with( 'title', 'Главная' );

    }

}
