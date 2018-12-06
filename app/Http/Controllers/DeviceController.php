<?php

namespace App\Http\Controllers;

use App\Classes\Devices;
use App\Classes\Asterisk;
use App\Models\File;
use App\Models\TicketManagement;
use App\Traits\Logs;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;

class DeviceController extends Controller
{

    use Logs;

    const CACHE_LIFE_MINUTES = 60;
    const UPDATES_LIFE_SECONDS = 10;

    public function __construct ()
    {
        \Debugbar::disable();
    }

    public function index ( Request $request, $route )
    {
        return $this->success( 'Hello, World!' );
    }

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

        if ( ! $user->can( 'rest.auth' ) )
        {
            $output = trans('device.denied' );
            $httpCode = 403;
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

        if ( ! $user->can( 'rest.auth' ) )
        {
            \Auth::logout();
            return $this->error( trans('device.denied' ), 403 );
        }

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

        if ( ! \Auth::user()->can( 'rest.tickets.show' ) )
        {
            return $this->error( trans('device.denied' ), 403 );
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
                    'ticket.type.category',
                    'ticket.calls'
                )
                ->take( 10 )
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

        if ( ! \Auth::user()->can( 'rest.tickets.show' ) )
        {
            return $this->error( trans('device.denied' ), 403 );
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
                'ticket.type.category',
                'ticket.calls'
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

        if ( ! \Auth::user()->can( 'rest.contacts.show' ) )
        {
            return $this->error( trans('device.denied' ), 403 );
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

        if ( ! \Auth::user()->can( 'rest.position' ) )
        {
            return $this->error( trans('device.denied' ), 403 );
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

    public function complete ( Request $request )
    {

        $validation = \Validator::make( $request->all(), [
            'token'         => 'required',
            'id'            => 'required|integer',
            'force'         => 'boolean',
            'files.*'       => 'file|mimes:jpg,jpeg,png,bmp,webp|size:1000',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }

        if ( ! $this->authToken( $request, $data, $httpCode ) )
        {
            return $this->error( $data, $httpCode );
        }

        if ( ! \Auth::user()->can( 'rest.tickets.edit' ) )
        {
            return $this->error( trans('device.denied' ), 403 );
        }

        $ticketManagement = TicketManagement
            ::mine()
            ->find( $request->get( 'id' ) );

        if ( ! $ticketManagement )
        {
            return $this->error( 'Заявка не найдена', 404 );
        }

        \DB::beginTransaction();

        $files = $request->allFiles();

        if ( $ticketManagement->needAct() )
        {
            if ( ! count( $files ) )
            {
                return $this->error( 'Необходимо прикрепить файл(ы)' );
            }
            $res = $ticketManagement->changeStatus( 'completed_with_act', $request->get( 'force', false ) );
            if ( $res instanceof MessageBag )
            {
                return $this->error( $res->first() );
            }
        }
        else
        {
            $res = $ticketManagement->changeStatus( 'completed_without_act', $request->get( 'force', false ) );
            if ( $res instanceof MessageBag )
            {
                return $this->error( $res->first() );
            }
        }

        foreach ( $files as $_file )
        {
            $path = Storage::putFile( 'files', $_file );
            $file = File::create([
                'model_id'      => $ticketManagement->id,
                'model_name'    => get_class( $ticketManagement ),
                'path'          => $path,
                'name'          => $_file->getClientOriginalName()
            ]);
            if ( $file instanceof MessageBag )
            {
                return $this->error( $file->first() );
            }
            $file->save();
            $file->parent->addLog( 'Загрузил файл "' . $file->name . '"' );
        }

        \DB::commit();

        return $this->success( 'OK' );

    }

    public function getPhone ( Request $request )
    {

        $validation = \Validator::make( $request->all(), [
            'id'            => 'required|integer',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }

        $ticketManagement = TicketManagement::find( $request->get( 'id' ) );

        if ( ! $ticketManagement )
        {
            return $this->error( 'Заявка не найдена', 404 );
        }

        $phone = $request->get( 'phone', $ticketManagement->ticket->phone );

        return response( $phone, 200, [
            'Content-Type' => 'text/plain'
        ]);

    }
	
	public function call ( Request $request )
	{
	
		$validation = \Validator::make( $request->all(), [
            'token'         => 'required',
            'id'            => 'required|integer',
            'source'        => 'required|digits:10',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }

        if ( ! $this->authToken( $request, $data, $httpCode ) )
        {
            return $this->error( $data, $httpCode );
        }

        if ( ! \Auth::user()->can( 'rest.tickets.call' ) )
        {
            return $this->error( trans('device.denied' ), 403 );
        }

        $ticketManagement = TicketManagement
            ::mine()
            ->find( $request->get( 'id' ) );

        if ( ! $ticketManagement )
        {
            return $this->error( 'Заявка не найдена', 404 );
        }
		
		$number_from = mb_substr( preg_replace( '/\D/', '', $request->get( 'source', '' ) ), -10 );
        #$number_to = $ticketManagement->ticket->phone;
		$number_to = 9647269122;

		\DB::beginTransaction();

		$ticketCall = $ticketManagement->ticket->createCall( $number_from, $number_to );
		if ( $ticketCall instanceof MessageBag )
        {
            return $this->error( $ticketCall->first() );
        }

        $asterisk = new Asterisk();
        if ( ! $asterisk->originate( $number_from, $number_to, 'outgoing-autodial' ) )
        {
            return $this->error( $asterisk->last_result );
        }

        \DB::commit();
		
		return $this->success( 'OK' );
	
	}

    public function calls ( Request $request )
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

        if ( ! \Auth::user()->can( 'rest.tickets.show' ) )
        {
            return $this->error( trans('device.denied' ), 403 );
        }

        $ticketManagement = TicketManagement
            ::mine()
            ->find( $request->get( 'id' ) );

        if ( ! $ticketManagement )
        {
            return $this->error( 'Заявка не найдена', 404 );
        }

        $calls = [];
        foreach ( $ticketManagement->ticket->calls as $call )
        {
            $calls[] = [
                'datetime' => $call->created_at->timestamp,
                'fullname' => $call->author->getName(),
                'number_from' => $call->agent_number,
                'number_to' => $call->call_phone,
            ];
        }

        $this->addLog( 'Запросил список звонков по заявке ' . $ticketManagement->getTicketNumber() );

        return $this->success( $calls );

    }

    public function comment ( Request $request )
    {

        if ( ! $this->authToken( $request, $data, $httpCode ) )
        {
            return $this->error( $data, $httpCode );
        }

        if ( ! \Auth::user()->can( 'rest.tickets.comment' ) )
        {
            return $this->error( trans('device.denied' ), 403 );
        }

        $validation = \Validator::make( $request->all(), [
            'id'            => 'required|integer',
            'text'          => 'required|string|max:1000',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }

        $ticketManagement = TicketManagement
            ::mine()
            ->find( $request->get( 'id' ) );

        if ( ! $ticketManagement )
        {
            return $this->error( 'Заявка не найдена', 404 );
        }

        $res = $ticketManagement->ticket->addComment( $request->get( 'text' ) );
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