<?php

namespace App\Http\Controllers;

use App\Classes\Devices;
use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Traits\Logs;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;

class DeviceController extends Controller
{

    use Logs;

    const CACHE_LIFE_MINUTES = 60;
    const UPDATES_LIFE_SECONDS = 10;

    private function authToken ( Request $request, & $output = null, & $httpCode = 200 ) : bool
    {

        $timestamp = Carbon::now()->timestamp - self::UPDATES_LIFE_SECONDS;

        $validation = \Validator::make( $request->all(), [
            'token'         => 'required',
        ]);

        if ( $validation->fails() )
        {
            foreach ( $validation->errors() as $error )
            {
                $output = $error->getMessage();
                $httpCode = 400;
                return false;
            }
        }

        $token = $request->get( 'token' );

        if ( ! \Cache::has( 'device.token.' . $token ) )
        {
            $output = trans('device.token' );
            $httpCode = 403;
            return false;
        }

        $data = \Cache::get( 'device.token.' . $token );

        $user = User::find( $data[ 0 ] );

        if ( ! $user )
        {
            $output = trans('device.user_not_found' );
            $httpCode = 400;
            return false;
        }

        if ( ! $user->isActive() )
        {
            $output = trans('device.user_not_active' );
            $httpCode = 400;
            return false;
        }

        \Auth::login( $user );

        $output = $data;
        $data[ 1 ] = $timestamp;

        \Cache::put( 'device.token.' . $token, $data, self::CACHE_LIFE_MINUTES );
        \Cache::put( 'device.user.' . $user->id, $token, self::CACHE_LIFE_MINUTES );

        return true;

    }

    public function auth ( Request $request ) : Response
    {

        $timestamp = Carbon::now()->timestamp - self::UPDATES_LIFE_SECONDS;

        $validation = \Validator::make( $request->all(), [
            'email'         => 'required|email',
            'password'      => 'required|min:3|max:50',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }

        if ( ! \Auth::guard()->attempt( $request->toArray() ) )
        {
            return $this->error( trans('auth.failed' ), 403 );
        }

        $user = \Auth::user();

        $token = md5( $user->id . $user->getName() . time() );

        if ( \Cache::has( 'device.user.' . $user->id ) )
        {
            $old_token = \Cache::get( 'device.user.' . $user->id );
            if ( \Cache::has( 'device.token.' . $old_token ) )
            {
                \Cache::forget( 'device.token.' . $old_token );
            }
        }

        \Cache::put( 'device.token.' . $token, [ $user->id, $timestamp ], self::CACHE_LIFE_MINUTES );
        \Cache::put( 'device.user.' . $user->id, $token, self::CACHE_LIFE_MINUTES );

        $this->addLog( 'Авторизовался' );

        return $this->success([
            'id'            => $user->id,
            'fullname'      => $user->getName(),
            'token'         => $token
        ]);

    }

    public function tickets ( Request $request ) : Response
    {

        if ( ! $this->authToken( $request, $data, $httpCode ) )
        {
            return $this->error( $data, $httpCode );
        }

        if ( \Cache::has( 'device.tickets.' . \Auth::user()->id ) )
        {
            $tickets = \Cache::get( 'device.tickets.' . \Auth::user()->id );
        }
        else
        {
            $tickets = TicketManagement
                ::mine()
                ->notFinaleStatuses()
                ->where( 'status_code', '!=', 'draft' )
                //->orderBy( 'id', 'desc' )
                ->with(
                    'ticket',
                    'ticket.comments',
                    'ticket.comments.author',
                    'ticket.building',
                    'ticket.author',
                    'ticket.type',
                    'ticket.type.category'
                )
                ->get();
            $tickets = Devices::ticketsInfo( $tickets );
            \Cache::put( 'device.tickets.' . \Auth::user()->id, $tickets, self::CACHE_LIFE_MINUTES );
        }

        $this->addLog( 'Запросил список заявок' );

        return $this->success( $tickets );

    }

    public function updates ( Request $request ) : Response
    {

        if ( ! $this->authToken( $request, $data, $httpCode ) )
        {
            return $this->error( $data, $httpCode );
        }

        $response = [];

        $ticketsAdded = TicketManagement
            ::mine()
            ->notFinaleStatuses()
            ->where( 'created_at', '>=', date( 'Y-m-d H:i:s', $data[ 1 ] ) )
            ->with(
                'ticket',
                'ticket.comments',
                'ticket.comments.author',
                'ticket.building',
                'ticket.author',
                'ticket.type',
                'ticket.type.category'
            )
            ->get();

        $response[ 'added' ] = Devices::ticketsInfo( $ticketsAdded );

        $ticketsUpdated = TicketManagement
            ::mine()
            ->notFinaleStatuses()
            ->where( 'updated_at', '>=', date( 'Y-m-d H:i:s', $data[ 1 ] ) )
            ->whereRaw( 'updated_at != created_at' )
            ->with(
                'ticket',
                'ticket.comments',
                'ticket.comments.author',
                'ticket.building',
                'ticket.author',
                'ticket.type',
                'ticket.type.category'
            )
            ->get();

        $response[ 'updated' ] = Devices::ticketsInfo( $ticketsUpdated );

        /*$ticketsDeleted = TicketManagement
            ::mine()
            ->withTrashed()
            ->where( 'deleted_at', '>=', date( 'Y-m-d H:i:s', $data[ 1 ] ) )
            ->pluck( 'id' )
            ->toArray();

        $response[ 'deleted' ] = $ticketsDeleted;*/
        $response[ 'deleted' ] = [];

        $this->addLog( 'Запросил список изменений' );

        return $this->success( $response );

    }

    public function contacts ( Request $request )
    {

        if ( ! $this->authToken( $request, $data, $httpCode ) )
        {
            return $this->error( $data, $httpCode );
        }

        if ( \Cache::has( 'device.contacts.' . \Auth::user()->id ) )
        {
            $contacts = \Cache::get( 'device.contacts.' . \Auth::user()->id );
        }
        else
        {
            $contacts = [];
            $count = 0;
            foreach ( \Auth::user()->managements as $management )
            {
                foreach ( $management->executors as $executor )
                {
                    $contacts[] = [
                        'fullname'      => $executor->name,
                        'phone'         => $executor->phone
                    ];
                    if ( ++ $count >= 5 )
                    {
                        break 2;
                    }
                }
            }
            \Cache::put( 'device.contacts.' . \Auth::user()->id, $contacts, self::CACHE_LIFE_MINUTES );
        }

        $this->addLog( 'Запросил список контактов' );

        return $this->success( $contacts );

    }

    public function position ( Request $request )
    {

        if ( ! $this->authToken( $request, $data, $httpCode ) )
        {
            return $this->error( $data, $httpCode );
        }

        $validation = \Validator::make( $request->all(), [
            'lon'           => 'required|numeric',
            'lat'           => 'required|numeric',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }

        $res = $user = \Auth::user()->setPosition( $request->get( 'lon' ), $request->get( 'lat' ) );
        if ( $res instanceof MessageBag )
        {
            return $this->error( $res->first() );
        }

        $this->addLog( 'Сообщил о своем местоположении' );

        return $this->success( 'OK' );

    }

    /*public function complete ( Request $request )
    {

        $validation = \Validator::make( $request->all(), [
            'token'         => 'required',
            'id'            => 'required|integer',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }

        if ( ! $this->authToken( $request, $data, $httpCode ) )
        {
            return $this->error( $data, $httpCode );
        }

        $tickets = TicketManagement
            ::mine()
            ->whereHas( 'executor', function ( $executor )
            {
                return $executor
                    ->where( 'id', '=',  );
            })
            ->where( 'status_code', '!=', 'draft' )
            ->groupBy( 'phone' )
            ->get();

        $user = \Auth::user();
        $user->lon = $request->get( 'lon' );
        $user->lat = $request->get( 'lat' );
        $user->save();

        return $this->success( 'OK' );

    }*/

    public function comment ( Request $request )
    {

        if ( ! $this->authToken( $request, $data, $httpCode ) )
        {
            return $this->error( $data, $httpCode );
        }

        $validation = \Validator::make( $request->all(), [
            'id'            => 'required|integer',
            'text'          => 'required|string|max:1000',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }

        $ticket = Ticket
            ::mine()
            ->find( $request->get( 'id' ) );

        if ( ! $ticket )
        {
            return $this->error( 'Заявка не найдена' );
        }

        $res = $ticket->addComment( $request->get( 'text' ) );
        if ( $res instanceof MessageBag )
        {
            return $this->error( $res->first() );
        }

        return $this->success( 'OK' );

    }

    public function clearCache ( Request $request )
    {

        if ( ! $this->authToken( $request, $data, $httpCode ) )
        {
            return $this->error( $data, $httpCode );
        }

        \Cache::forget( 'device.contacts.' . \Auth::user()->id );
        \Cache::forget( 'device.tickets.' . \Auth::user()->id );

        $this->addLog( 'Очистил кеш' );

        return $this->success( 'OK' );

    }

    private function error ( $error, $httpCode = 400 ) : Response
    {
        return response( compact( 'error' ), $httpCode );
    }

    private function success ( $response ) : Response
    {
        return response( $response, 200 );
    }

}