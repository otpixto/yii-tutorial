<?php

namespace App\Http\Controllers;

use App\Jobs\SendStream;
use App\Models\File;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;

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
            ->with( 'reply_id', $request->get( 'reply_id' ) )
            ->with( 'with_file', $request->get( 'with_file' ) );
    }
	
	public function store ( Request $request )
    {

        $rules = [
            'model_id'      => 'required|integer',
            'model_name'    => 'required',
            'reply_id'      => 'nullable|integer',
            'text'          => 'required|max:1000',
        ];
        
		$this->validate( $request, $rules );

        $comment = Comment::create( $request->all() );
        $comment->save();
        $log = $comment->addLog( 'Добавил комментарий' );
        if ( $log instanceof MessageBag )
        {
            return redirect()->back()->withErrors( $log );
        }

        if ( $comment->parent instanceof Ticket )
        {

            $ticket = $comment->parent;

            foreach ( $ticket->managements as $ticketManagement )
            {

                if ( \Config::get( 'telegram.active' ) )
                {

                    $message = '<em>Добавлен комментарий</em>' . PHP_EOL . PHP_EOL;

                    $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                    $message .= 'Тип заявки: ' . ( $ticket->type->name ?? '-' ) . PHP_EOL;
                    $message .= 'Автор комментария: ' . $comment->author->getName( true ) . PHP_EOL;

                    $message .= PHP_EOL . $comment->text . PHP_EOL;

                    $message .= PHP_EOL . $ticketManagement->getUrl() . PHP_EOL;

                    $ticketManagement->sendTelegram( $message );

                }

            }

            $ticket->updated_at = Carbon::now()->toDateTimeString();
            $ticket->save();

            $this->dispatch( new SendStream( 'comment', $ticket ) );

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
                $log = $file->addLog( 'Загрузил файл "' . $file->name . '"' );
                if ( $log instanceof MessageBag )
                {
                    return redirect()->back()->withErrors( $log );
                }
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
                $comment->addLog( 'Комментарий удален' );
                $comment->delete();
            }
        }

    }

}
