<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;

class RateController extends Controller
{
	
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct ()
    {
        $this->middleware('auth' );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function form ( Request $request )
    {
        return view( 'modals.rate' )
			->with( 'rate', $request->get( 'rate' ) )
			->with( 'ticket_id', $request->get( 'ticket_id' ) );
    }
	
	public function store ( Request $request )
    {
        
		$this->validate( $request, Comment::$rules );
		
		$comment = Comment::create( $request->all() );

		if ( $request->hasFile( 'file' ) )
        {
            $path = Storage::putFile( 'files', $request->file( 'file' ) );
            $file = File::create([
                'model_id'      => $comment->id,
                'model_name'    => get_class( $comment ),
                'path'          => $path
            ]);
        }

		return redirect()->back()->with( 'success', 'Комментарий добавлен' );
		
    }
	
}
