<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Ticket;
use App\Models\TicketManagement;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;

class FilesController extends Controller
{

    public function __construct ()
    {
        $this->middleware('auth' );
    }

    public function download ( Request $request )
    {

        $file = File::find( $request->get( 'id' ) );
        if ( ! $file )
        {
            return redirect()->back()->withErrors( [ 'Файл не найден' ] );
        }

        if ( $file->getToken() != $request->get( 'token' ) )
        {
            return redirect()->back()->withErrors( [ 'Неверный токен' ] );
        }

        try
        {
            $path = storage_path( 'app/' . $file->path );
            return response()->download( $path, $file->name );
        }
        catch ( \Exception $e )
        {
            return redirect()->back()->withErrors( $e->getMessage() );
        }

    }

    public function form ( Request $request )
    {
        return view( 'modals.files' )
            ->with( 'model_id', $request->get( 'model_id' ) )
            ->with( 'model_name', $request->get( 'model_name' ) )
            ->with( 'status', $request->get( 'status' ) );
    }

    public function store ( Request $request )
    {

        if ( $request->hasFile( 'files' ) )
        {
            \DB::beginTransaction();
            foreach ( $request->file( 'files' ) as $_file )
            {
                $path = Storage::putFile( 'files', $_file );
                $file = File::create([
                    'model_id'      => $request->get( 'model_id' ),
                    'model_name'    => $request->get( 'model_name' ),
                    'path'          => $path,
                    'name'          => $_file->getClientOriginalName()
                ]);
                if ( $file instanceof MessageBag )
                {
                    return redirect()->back()->withErrors( $file );
                }
                $file->save();
                $log = $file->parent->addLog( 'Загружен файл "' . $file->name . '"' );
                if ( $log instanceof MessageBag )
                {
                    return redirect()->back()->withErrors( $log );
                }
            }
            if ( ! empty( $request->get( 'status' ) ) && $request->get( 'model_name' ) == TicketManagement::class && ! in_array( $file->parent->status_code, Ticket::$final_statuses ) )
            {
                $ticketManagement = TicketManagement::find( $request->get( 'model_id' ) );
                if ( $ticketManagement )
                {
                    $res = $ticketManagement->changeStatus( $request->get( 'status' ), true );
                    if ( $res instanceof MessageBag )
                    {
                        return redirect()->back()->withErrors( $res );
                    }
                }
            }
            \DB::commit();
        }

        return redirect()->back()->with( 'success', 'Файл(ы) добавлен(ы)' );

    }
	
}
