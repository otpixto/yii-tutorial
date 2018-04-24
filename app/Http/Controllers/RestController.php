<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PhoneSession;
use App\Models\Region;
use App\Models\Ticket;
use App\Models\TicketCall;
use Illuminate\Http\Request;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class RestController extends Controller
{

    private $is_auth = false;

    private $logs;

    private $errors = [
        100         => 'Авторизация провалена',
        101         => 'Авторизованный телефон не найден',
        102         => 'Пользователь отключен',
        103         => 'Для данного пользователя уже создан черновик',
        104         => 'Запись о звонке не найдена в БД',
        105         => 'Заявитель не найден',
    ];

    public function __construct ( Request $request )
    {
        $this->logs = new Logger( 'REST' );
        $this->logs->pushHandler( new StreamHandler( storage_path( '/logs/rest.log' ) ) );
        $this->logs->addInfo( 'Запрос от ' . $request->ip(), $request->all() );
    }

    public function index ()
    {

    }

    public function customer ( Request $request )
    {

        if ( ! $this->auth( $request ) )
        {
            return $this->error( 100 );
        }

        $call_phone = mb_substr( $request->get( 'call_phone' ), -10 );

        $customer = Customer
            ::where( 'phone', '=', $call_phone )
            ->orWhere( 'phone2', '=', $call_phone )
            ->orderBy( 'id', 'desc' )
            ->first();

        if ( ! $customer )
        {
            return $this->error( 105 );
        }

        $response = [
            'address' => $customer->getAddress(),
            'name' => $customer->getName()
        ];

        return $this->success( $response );

    }

    public function createOrUpdateCallDraft ( Request $request )
    {

        if ( ! $this->auth( $request ) )
        {
            return $this->error( 100 );
        }

        $session = PhoneSession
			::notClosed()
            ->where( 'number', '=', $request->get( 'number' ) )
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
            ::draft( $user->id )
            ->first();

        $phone = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone' ) ), -10 );
        $phone_office = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone_office' ) ), -10 );

        $region = Region
            ::mine( $user )
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

        return $this->success( $region->name ?? '' );

    }

    public function ticketCall ( Request $request )
    {

        if ( ! $this->auth( $request ) )
        {
            return $this->error( 100 );
        }

        $ticketCall = TicketCall
            ::whereNull( 'call_id' )
            ->where( 'agent_number', '=', $request->get( 'agent_number' ) )
            ->where( 'call_phone', '=', $request->get( 'call_phone' ) )
            ->first();

        if ( ! $ticketCall )
        {
            return $this->error( 104 );
        }

        $ticketCall->call_id = $request->get( 'call_id' );
        $ticketCall->save();

        return $this->success( '' );

    }

    private function error ( $code )
    {
        $this->logs->addError( 'Ошибка', [ $code, $this->errors[ $code ] ] );
        return [
            'success'   => false,
            'code'      => $code,
            'message'   => $this->errors[ $code ]
        ];
    }

    private function success ( $message )
    {
        $this->logs->addInfo( 'Успешно', is_array( $message ) ? $message : [ $message ] );
        return [
            'success'   => true,
            'message'   => $message
        ];
    }

    private function auth ( Request $request )
    {
        if ( $this->is_auth ) return true;
        $this->logs->addInfo( 'Авторизация', $request->all() );
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
        $arr[] = \Config::get( 'rest.password' );
        $hash = mb_strtolower( $hash );
        $_hash = mb_strtolower( md5( implode( '|', $arr ) ) );
        $status = $hash == $_hash;
        $this->is_auth = $status;
        return $status;
    }

}
