<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Ticket;
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
            ->with( 'origin_model_id', $request->get( 'origin_model_id' ) )
            ->with( 'origin_model_name', $request->get( 'origin_model_name' ) )
            ->with( 'with_file', $request->get( 'with_file' ) );
    }
	
	public function store ( Request $request )
    {
        
		$this->validate( $request, Comment::$rules );

        $comment = Comment::create( $request->all() );
        $comment->save();

		if ( $request->get( 'origin_model_name' ) == Ticket::class )
        {

            $ticket = Ticket::find( $request->get( 'origin_model_id' ) );

            $author = $comment->author->getName();

            if ( $comment->author->hasRole( 'operator' ) )
            {
                $author = '<i>[Оператор ЕДС]</i> ' . $author;
            }
            elseif ( $comment->author->hasRole( 'management' ) && $comment->author->management )
            {
                $author = '<i>[' . $comment->author->management->name . ']</i> ' . $author;
            }

            $message = '<em>Добавлен комментарий</em>' . PHP_EOL . PHP_EOL;

            $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
            $message .= 'Автор комментария: ' . $author . PHP_EOL;

            $message .= PHP_EOL . $comment->text . PHP_EOL;

            $message .= PHP_EOL . route( 'tickets.show', $ticket->id ) . PHP_EOL;

            $ticket->sendTelegram( $message );

            /*$group = $ticket->group()->where( 'id', '!=', $ticket->id )->get();
            if ( $group->count() )
            {
                foreach ( $group as $row )
                {
                    $row->addComment( $comment->text );
                }
            }*/

        }

        if ( $request->hasFile( 'files' ) )
        {
            foreach ( $request->file( 'files' ) as $_file )
            {
                $path = Storage::putFile( 'files', $_file );
                $file = File::create([
                    'model_id'      => $comment->id,
                    'model_name'    => get_class( $comment ),
                    'path'          => $path,
                    'name'          => $_file->getClientOriginalName()
                ]);
                $file->save();
            }
        }

		return redirect()->back()->with( 'success', 'Комментарий добавлен' );
		
    }
	
}
