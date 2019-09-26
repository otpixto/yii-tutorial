<?php

namespace App\Http\Controllers;

use App\Classes\Asterisk;
use App\Models\PhoneSession;
use App\Models\Provider;
use App\Models\ProviderPhone;
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
        107         => 'Поставщик не определен',
        900         => 'Внутренняя ошибка',
    ];

    public function __construct ( Request $request )
    {
        \Debugbar::disable();
        $this->logs = new Logger( 'REST' );
        $this->logs->pushHandler( new StreamHandler( storage_path( 'logs/rest.log' ) ) );
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
        $number = $auth->number;
        $user_id = $auth->user_id;
        $provider_id = $auth->provider_id;
        $auth->delete();
        $asterisk = $auth->provider->getAsterisk();
        $phoneSession = PhoneSession::create([
            'provider_id'   => $provider_id,
            'user_id'       => $user_id,
            'number'        => $number
        ]);
        if ( $phoneSession instanceof MessageBag )
        {
            return $this->error( 900 );
        }
        $phoneSession->save();
        $log = $phoneSession->addLog( 'Телефонная сессия началась' );
        if ( $log instanceof MessageBag )
        {
            return $this->error( 900 );
        }
        if ( ! $asterisk->queueAddByExten( $number ) )
        {
            return $this->error( 106 );
        }
        \DB::commit();
        return $this->success();
    }

    public function customer ( Request $request )
    {

        if ( ! $this->auth( $request ) )
        {
            return $this->error( 100 );
        }

        $response = [
            'customer'          => null,
            'provider'          => null,
            'users'             => []
        ];

        $phone_office = mb_substr( $request->get( 'phone_office' ), -10 );

        if ( \Cache::has( 'provider.phone.' . $phone_office ) )
        {
            $providerPhone = \Cache::get( 'provider.phone.' . $phone_office );
        }
        else
        {
            $providerPhone = ProviderPhone
                ::where( 'phone', '=', $phone_office )
                ->first();
            \Cache::put( 'provider.phone.' . $phone_office, $providerPhone, 1440 );
        }

        if ( $providerPhone )
        {
            $response[ 'provider' ] = $providerPhone->name;
            if ( $providerPhone->provider )
            {
                $call_phone = mb_substr( $request->get( 'call_phone' ), -10 );
                if ( \Cache::has( 'provider.customer.' . $providerPhone->provider_id . '.' . $call_phone ) )
                {
                    $customer = \Cache::get( 'provider.customer.' . $providerPhone->provider_id . '.' . $call_phone );
                }
                else
                {
                    $customer = $providerPhone->provider->customers()
                        ->where( 'phone', '=', $call_phone )
                        ->orWhere( 'phone2', '=', $call_phone )
                        ->orderBy( 'id', 'desc' )
                        ->first();
                    \Cache::put( 'provider.customer.' . $providerPhone->provider_id . '.' . $call_phone, $customer, 1440 );
                }
                if ( $customer )
                {
                    $response[ 'customer' ] = [
                        'building' => $customer->getActualAddress(),
                        'name' => $customer->getName(),
                    ];
                }
                $response[ 'users' ] = $providerPhone->provider->phoneSessions()->pluck( PhoneSession::$_table . '.user_id' )->toArray();
            }
        }

        return $this->success( $response );

    }

    public function createOrUpdateCallDraft ( Request $request )
    {

        if ( ! $this->auth( $request ) )
        {
            return $this->error( 100 );
        }

        $number = mb_substr( $request->get( 'number' ), -10 );
        $phone_office = mb_substr( $request->get( 'phone_office' ), -10 );
        $phone = mb_substr( preg_replace( '/\D/', '', $request->get( 'phone' ) ), -10 );
        $interface = $request->get( 'interface' );

        $session = PhoneSession
            ::notClosed()
            ->where( 'channel', '=', $interface )
            ->first();
        if ( ! $session )
        {
            return $this->error( 101 );
        }
        if ( ! $session->user || ! $session->user->isActive() )
        {
            return $this->error( 102 );
        }

        $provider = $session->provider;
        $user = $session->user;

        if ( ! $provider )
        {
            return $this->error( 107 );
        }

        $providerPhone = $provider
            ->phones()
            ->where( 'phone', '=', $phone_office )
            ->first();

        $response = [
            'ticket'                => null,
            'provider'              => null,
            'provider_phone'        => null,
            'user'                  => $user->id
        ];

        $draft = Ticket
            ::draft( $user->id, $provider->id )
            ->first();

        if ( ! $draft )
        {
            $draft = new Ticket();
            $draft->status_code = 'draft';
            $draft->status_name = Ticket::$statuses[ 'draft' ];
            $draft->author_id = $user->id;
            $draft->provider_id = $provider->id;
        }

        $draft->phone = $phone;
        $draft->call_phone = $draft->phone;
        $draft->call_id = $request->get( 'call_id' );
        $draft->call_description = $providerPhone->name ?? null;

        $draft->save();

        $response[ 'provider' ] = $provider->name;
        $response[ 'provider_phone' ] = $providerPhone->name ?? null;
        $response[ 'ticket' ] = $draft->id;

        return $this->success( $response );

    }

    public function user ( Request $request )
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

        $response = [
            'user' => $user->id
        ];

        return $this->success( $response );

    }

    public function ticketCall ( Request $request )
    {

        $ticketCall = TicketCall::find( $request->get( 'ticket_call_id' ) );
        if ( $ticketCall )
        {
            $ticketCall->call_id = $request->get( 'uniqueid' );
            $ticketCall->save();
        }

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
        if ( ! $hash ) return false;
        $data = $request->all();
        unset( $data[ 'hash' ] );
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
