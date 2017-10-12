<?php

namespace App\Http\Controllers;

use App\Models\PhoneSession;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RestController extends Controller
{

    const REST_PASS = '4AZVdsDFTw';

    private $is_auth = false;

    private $errors = [
        100         => 'Авторизация провалена',
        101         => 'Авторизованный телефон не найден',
        102         => 'Пользователь отключен',
        103         => 'Для данного пользователя уже создан черновик'
    ];

    public function __construct ()
    {
        //
    }

    public function createOrUpdateCallDraft ( Request $request )
    {

        if ( ! $this->auth( $request ) )
        {
            return $this->error( 100 );
        }

        $session = PhoneSession
            ::where( 'number', '=', $request->get( 'number' ) )
            ->first();
        if ( ! $session )
        {
            return $this->error( 101 );
        }
        if ( ! $session->user || ! $session->user->active )
        {
            return $this->error( 102 );
        }

        $draft = Ticket
            ::where( 'author_id', '=', $session->user->id )
            ->where( 'status_code', '=', 'draft' )
            ->first();

        if ( ! $draft )
        {
            $draft = new Ticket();
            $draft->status_code = 'draft';
            $draft->status_name = Ticket::$statuses[ 'draft' ];
            $draft->author_id = $session->user->id;
            $draft->phone = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $request->get( 'phone' ) ) ), -10 );
            $draft->call_phone = $draft->phone;
            $draft->call_at = Carbon::now()->toDateTimeString();
        }
        else
        {
            $draft->phone = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $request->get( 'phone' ) ) ), -10 );
            $draft->call_phone = $draft->phone;
            $draft->call_at = Carbon::now()->toDateTimeString();
        }

        $draft->save();

        return $this->success( '' );

    }

    private function error ( $code )
    {
        return [
            'success'   => false,
            'code'      => $code,
            'message'   => $this->errors[ $code ]
        ];
    }

    private function success ( $message )
    {
        return [
            'success'   => true,
            'message'   => $message
        ];
    }

    private function auth ( Request $request )
    {
        if ( $this->is_auth ) return true;
        $hash = $request->get( 'hash', null );
        if ( !$hash ) return false;
        $data = $request->all();
        unset( $data['hash'] );
        ksort( $data );
        $arr = [];
        foreach ( $data as $key => $val )
        {
            $arr[] = $key . '=' . $val;
        }
        $arr[] = self::REST_PASS;
        $hash = mb_strtolower( $hash );
        $_hash = mb_strtolower( md5( implode( '|', $arr ) ) );
        $status = $hash == $_hash;
        $this->is_auth = $status;
        return $status;
    }

}
