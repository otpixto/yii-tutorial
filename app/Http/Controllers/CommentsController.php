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
            ->with( 'with_file', $request->get( 'with_file' ) );
    }
	
	public function store ( Request $request )
    {
        
		$this->validate( $request, Comment::$rules );

		if ( $request->get( 'model_name' ) == 'App\Models\Ticket' )
        {

            $ticket = Ticket::find( $request->get( 'model_id' ) );
            $comment = $ticket->addComment( $request->get( 'text' ) );

            if ( $comment->author->hasRole( 'operator' ) )
            {
                $author = '<i>[Оператор ЕДС]</i> ' . $comment->author->getName();
            }
            elseif ( $comment->author->hasRole( 'management' ) && $comment->author->management )
            {
                $author = '<i>[' . $comment->author->management->name . ']</i> ' . $comment->author->getName();
            }

            $message = '<em>Добавлено сообщение</em>' . PHP_EOL . PHP_EOL;

            $message .= '<b>Номер обращения: ' . $ticket->id . '</b>' . PHP_EOL;
            $message .= 'Автор сообщения: ' . $author . PHP_EOL;

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
        else
        {
            $comment = Comment::create( $request->all() );
            $comment->save();
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
