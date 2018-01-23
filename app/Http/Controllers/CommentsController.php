<?php

namespace App\Http\Controllers;

use App\Jobs\SendStream;
use App\Models\File;
use App\Models\Ticket;
use App\Models\TicketManagement;
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

        if ( $comment->origin_model_name == Ticket::class )
        {

            $ticket = $comment->parentOriginal;

            foreach ( $ticket->managements as $ticketManagement )
            {

                if ( \Config::get( 'telegram.active' ) )
                {

                    $message = '<em>Добавлен комментарий</em>' . PHP_EOL . PHP_EOL;

                    $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                    $message .= 'Тип заявки: ' . $ticket->type->name . PHP_EOL;
                    $message .= 'Автор комментария: ' . $comment->author->getFullName() . PHP_EOL;

                    $message .= PHP_EOL . $comment->text . PHP_EOL;

                    $message .= PHP_EOL . $ticketManagement->getUrl() . PHP_EOL;

                    $ticketManagement->sendTelegram( $message );

                }

                $this->dispatch( new SendStream( 'comment', $ticketManagement ) );

            }


        }
        else if ( $comment->origin_model_name == TicketManagement::class )
        {

            $ticketManagement = $comment->parentOriginal;

            if ( \Config::get( 'telegram.active' ) )
            {

                $ticket = $ticketManagement->ticket;

                $message = '<em>Добавлен комментарий</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Тип заявки: ' . $ticket->type->name . PHP_EOL;
                $message .= 'Автор комментария: ' . $comment->author->getFullName() . PHP_EOL;

                $message .= PHP_EOL . $comment->text . PHP_EOL;

                $message .= PHP_EOL . $ticketManagement->getUrl() . PHP_EOL;

                $ticketManagement->sendTelegram( $message );

            }

            $this->dispatch( new SendStream( 'comment', $ticketManagement ) );

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

        $success = 'Комментарий успешно добавлен';

        if ( $request->ajax() )
        {
            return compact( 'success' );
        }
        else
        {
            return redirect()->back()->with( 'success', $success );
        }
		
    }

    public function delete ( Request $request )
    {

        $comment_id = (int) $request->get( 'comment_id', 0 );
        if ( $comment_id && \Auth::user()->can( 'tickets.comments_delete' ) )
        {
            $comment = Comment::find( $comment_id );
            if ( $comment )
            {
                if ( $comment->origin_model_name == TicketManagement::class && $comment->parentOriginal )
                {
                    $this->dispatch( new SendStream( 'comment', $comment->parentOriginal ) );
                }
                $comment->addLog( 'Комментарий удален' );
                $comment->delete();
            }
        }

    }

}
