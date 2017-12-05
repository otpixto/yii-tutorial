<?php

namespace App\Http\Controllers;

use App\Models\PhoneSession;
use App\Models\Region;
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
        if ( ! $session->user || ! $session->user->isActive() )
        {
            return $this->error( 102 );
        }

        $user = $session->user;

        $draft = Ticket
            ::where( 'author_id', '=', $user->id )
            ->where( 'status_code', '=', 'draft' )
            ->first();

        $phone = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone' ) ), -10 );
        $phone_office = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone_office' ) ), -10 );

        $region = $user
            ->regions()
            ->mine()
            ->whereHas( 'phones', function ( $q ) use ( $phone_office )
            {
                return $q
                    ->where( 'phone', '=', $phone_office );
            })
            ->first();

        if ( ! $draft )
        {
            $draft = new Ticket();
            $draft->status_code = 'draft';
            $draft->status_name = Ticket::$statuses[ 'draft' ];
            $draft->author_id = $user->id;
            $draft->phone = $phone;
            $draft->call_phone = $draft->phone;
            $draft->call_id = $request->get( 'call_id' );
        }
        else
        {
            $draft->phone = $phone;
            $draft->call_phone = $draft->phone;
            $draft->call_id = $request->get( 'call_id' );
        }

        if ( $region )
        {
            $draft->region_id = $region->id;
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
