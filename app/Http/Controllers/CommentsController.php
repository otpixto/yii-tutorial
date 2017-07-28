<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;

class CommentsController extends Controller
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
        return view( 'modals.comment' )
			->with( 'model_id', $request->get( 'model_id' ) )
			->with( 'model_name', $request->get( 'model_name' ) )
            ->with( 'with_file', $request->get( 'with_file' ) );
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
