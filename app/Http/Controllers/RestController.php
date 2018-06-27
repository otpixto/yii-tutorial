<?php

namespace App\Http\Controllers;

use App\Classes\Asterisk;
use App\Models\Customer;
use App\Models\PhoneSession;
use App\Models\Region;
use App\Models\Ticket;
use App\Models\TicketCall;
use App\Models\UserPhoneAuth;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
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
        106         => 'Астериск ответил ошибкой',
        900         => 'Внутренняя ошибка',
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

    public function phoneAuth ( Request $request )
    {
        $code = $request->get( 'code' );
        $auth = UserPhoneAuth
            ::where( 'code', '=', $code )
            ->first();
        if ( ! $auth || ( $auth->user && $auth->user->openPhoneSession ) )
        {
            return $this->error( 100 );
        }
        \DB::beginTransaction();
        $asterisk = new Asterisk();
        if ( ! $asterisk->queueAdd( $auth->number ) )
        {
            return $this->error( 106 );
        }
        $phoneSession = PhoneSession::create([
            'user_id'       => $auth->user_id,
            'number'        => $auth->number
        ]);
        if ( $phoneSession instanceof MessageBag )
        {
            $asterisk->queueRemove( $auth->number );
            return $this->error( 900 );
        }
        $phoneSession->save();
        $log = $phoneSession->addLog( 'Телефонная сессия началась' );
        if ( $log instanceof MessageBag )
        {
            $asterisk->queueRemove( $auth->number );
            return $this->error( 900 );
        }
        $auth->delete();
        \DB::commit();
        return $this->success();
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

        $phone_office = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone_office' ) ), -10 );

        $region = Region
            ::whereHas( 'phones', function ( $phones ) use ( $phone_office )
            {
                return $phones
                    ->where( 'phone', '=', $phone_office );
            })
            ->first();

        $response = [
            'address' => $customer->getAddress(),
            'name' => $customer->getName(),
            'region' => $region->name ?? '-',
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

    private function error ( $code = null )
    {
        $message = $this->errors[ $code ] ?? null;
        $this->logs->addError( 'Ошибка', [ $code, $message ] );
        return [
            'success'   => false,
            'code'      => $code,
            'message'   => $message
        ];
    }

    private function success ( $message = null )
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
