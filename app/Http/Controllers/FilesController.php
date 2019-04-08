<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\ProviderToken;
use App\Models\Ticket;
use App\Models\TicketManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;

class FilesController extends Controller
{

    public function download ( Request $request )
    {

        try
        {

            $this->validate( $request, [
                'id'                => 'required|integer',
                'token'             => 'required',
                'user_token'        => 'nullable',
            ]);

            $file = File::find( $request->get( 'id' ) );
            if ( ! $file )
            {
                return redirect()
                    ->back()
                    ->withErrors( [ 'Файл не найден' ] );
            }

            if ( $file->getToken() != $request->get( 'token' ) )
            {
                return redirect()
                    ->back()
                    ->withErrors( [ 'Некорректный токен файла' ] );
            }

            if ( $request->get( 'user_token' ) )
            {
                $providerToken = ProviderToken
                    ::where( 'token', '=', $request->get( 'user_token' ) )
                    ->whereHas( 'providerKey', function ( $providerKey )
                    {
                        return $providerKey
                            ->whereHas( 'provider' );
                    })
                    ->first();
                if ( ! $providerToken )
                {
                    return redirect()
                        ->back()
                        ->withErrors( [ 'Некорректный токен пользователя' ] );
                }
                if ( ! $providerToken->user || ! $providerToken->user->active )
                {
                    return redirect()
                        ->back()
                        ->withErrors( [ 'Пользователь не активен' ] );
                }
                \Auth::login( $providerToken->user );
            }
            else if ( ! \Auth::user() || ! \Auth::user()->active )
            {
                return redirect()
                    ->back()
                    ->withErrors( [ 'Пользователь не активен' ] );
            }

			if ( $file->parent )
			{
				if ( $file->parent->origin_model_name && $file->parent->parentOriginal )
				{
					$file->parent->parentOriginal->addLog( 'Скачал файл "' . $file->name . '"' );
				}
				else
				{
					$file->parent->addLog( 'Скачал файл "' . $file->name . '"' );
				}
			}

            return response()->download( storage_path( 'app/' . $file->path ), $file->name );

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

        if ( ! $request->hasFile( 'files' ) )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Файл не выбран' ] );
        }

        \DB::beginTransaction();
        foreach ( $request->file( 'files', [] ) as $_file )
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
            $file->parent->addLog( 'Загрузил файл "' . $file->name . '"' );
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

        return redirect()->back()->with( 'success', 'Файл(ы) добавлен(ы)' );

    }
	
}
