<?php

namespace App\Http\Controllers\Rest;

use App\Classes\Devices;
use App\Classes\Asterisk;
use App\Models\File;
use App\Models\ProviderToken;
use App\Models\TicketManagement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;

class DeviceController extends BaseController
{

    public function __construct ( Request $request )
    {
        $this->setLogs( storage_path( 'logs/rest_device.log' ) );
        parent::__construct( $request );
    }

    public function index ( Request $request, $route )
    {
        return $this->success( 'Hello, World!' );
    }

    public function login ( Request $request ) : Response
    {

        if ( ! $this->checkProviderKey( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        $validation = \Validator::make( $request->all(), [
            'email'         => 'required|email',
            'password'      => 'required|min:3|max:50',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }

        if ( ! \Auth::guard()->attempt( $request->only( 'email', 'password' ) ) )
        {
            return $this->error( trans('auth.failed' ), 403 );
        }

        $user = \Auth::user();

        $token = $this->genToken( $request );

        $providerToken = ProviderToken::create([
            'provider_key_id'       => $this->providerKey->id,
            'user_id'               => $user->id,
            'token'                 => $token,
            'http_user_agent'       => $request->server( 'HTTP_USER_AGENT', '' ),
            'ip'                    => $request->ip(),
        ]);

        $providerToken->providerKey->active_at = Carbon::now()->toDateTimeString();
        $providerToken->providerKey->save();

        $user->push_id = $request->get( 'push_id', null );
        $user->save();

        $this->addLog( 'Авторизовался' );

        return $this->success([
            'id'            => $user->id,
            'fullname'      => $user->getName(),
            'token'         => $providerToken->token
        ]);

    }

    public function tickets ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        if ( ! \Auth::user()->can( 'rest.tickets.show' ) )
        {
            return $this->error( trans('device.denied' ), 403 );
        }

        $validation = \Validator::make( $request->all(), [
            'page'                => 'nullable|integer',
            'per_page'            => 'nullable|integer',
            'ticket_id'           => 'nullable|integer',
            'date_from'           => 'nullable|date|date_format:Y-m-d',
            'date_to'             => 'nullable|date|date_format:Y-m-d',
            'building_id'         => 'nullable|integer',
            'status_code'         => 'nullable|string',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }

        $tickets = TicketManagement
            ::mine()
            ->notFinaleStatuses()
            ->where( 'status_code', '!=', 'draft' )
            ->whereHas( 'ticket', function ( $ticket ) use ( $request )
            {
                if ( $request->get( 'ticket_id' ) )
                {
                    $ticket
                        ->where( 'id', '=', $request->get( 'ticket_id' ) );
                }

                if ( $request->get( 'date_from' ) )
                {
                    $ticket
                        ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $request->get( 'date_from' ) )->toDateTimeString() ] );
                }

                if ( $request->get( 'date_to' ) )
                {
                    $ticket
                        ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $request->get( 'date_to' ) )->toDateTimeString() ] );
                }

                if ( $request->get( 'building_id' ) )
                {
                    $ticket
                        ->where( 'building_id', '=', $request->get( 'building_id' ) );
                }
                return $ticket;
            });

        if ( $request->get( 'status_code' ) )
        {
            $tickets
                ->where( 'status_code', '=', $request->get( 'status_code' ) );
        }

        $tickets = $tickets
            ->orderBy( 'id', 'desc' )
            ->paginate( config( 'pagination.per_page' ) );

        $tickets = Devices::ticketsInfo( $tickets );

        $this->addLog( 'Запросил список заявок' );

        return $this->success( $tickets );

    }

    public function updates ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        if ( ! \Auth::user()->can( 'rest.tickets.show' ) )
        {
            return $this->error( trans('device.denied' ), 403 );
        }

        $last_update = \Cache::tags( 'devices' )->get( 'devices.user.' . \Auth::user()->id . '.last_update', Carbon::now()->timestamp );
        \Cache::tags( 'devices' )->put( 'devices.user.' . \Auth::user()->id . '.last_update', Carbon::now()->timestamp );

        $response = [];

        $ticketsAdded = TicketManagement
            ::mine()
            ->notFinaleStatuses()
            ->where( 'created_at', '>=', date( 'Y-m-d H:i:s', $last_update ) )
            ->get();

        $response[ 'added' ] = Devices::ticketsInfo( $ticketsAdded );

        $ticketsUpdated = TicketManagement
            ::mine()
            ->notFinaleStatuses()
            ->where( 'updated_at', '>=', date( 'Y-m-d H:i:s', $last_update ) )
            ->whereRaw( 'updated_at != created_at' )
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

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        if ( ! \Auth::user()->can( 'rest.contacts.show' ) )
        {
            return $this->error( trans('device.denied' ), 403 );
        }

        if ( \Cache::tags( 'devices' )->has( 'devices.user.' . \Auth::user()->id . '.contacts' ) )
        {
            $contacts = \Cache::tags( 'devices' )->get( 'devices.user.' . \Auth::user()->id . '.contacts' );
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
            \Cache::tags( 'devices' )->put( 'devices.user.' . \Auth::user()->id . '.contacts', $contacts, 15 );
        }

        $this->addLog( 'Запросил список контактов' );

        return $this->success( $contacts );

    }

    public function position ( Request $request )
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
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

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

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

    public function inProcess ( Request $request )
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        $validation = \Validator::make( $request->all(), [
            'token'         => 'required',
            'id'            => 'required|integer',
            'force'         => 'boolean',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
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

        $res = $ticketManagement->changeStatus( 'in_process', $request->get( 'force', false ) );
        if ( $res instanceof MessageBag )
        {
            return $this->error( $res->first() );
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

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }
	
		$validation = \Validator::make( $request->all(), [
            'token'         => 'required',
            'id'            => 'required|integer',
            'number_from'   => 'required|digits:10',
            'number_to'     => 'required|digits:10',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
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
		
		$number_from = mb_substr( preg_replace( '/\D/', '', $request->get( 'number_from', '' ) ), -10 );
		$number_to = mb_substr( preg_replace( '/\D/', '', $request->get( 'number_to', '' ) ), -10 );
        #$number_to = $ticketManagement->ticket->phone;

		\DB::beginTransaction();

		$ticketCall = $ticketManagement->ticket->createCall( $number_from, $number_to );
		if ( $ticketCall instanceof MessageBag )
        {
            return $this->error( $ticketCall->first() );
        }

        $asterisk = new Asterisk();
		$rest_curl_url = config( 'rest.curl_url' ) . '/ticket-call?ticket_call_id=' . (int) $ticketCall->id;
        if ( ! $asterisk->originate( $number_from, $number_to, 'outgoing-autodial', $number_from, 1, $rest_curl_url ) )
        {
            return $this->error( $asterisk->last_result );
        }

        \DB::commit();
		
		return $this->success( 'OK' );
	
	}

    public function calls ( Request $request )
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        $validation = \Validator::make( $request->all(), [
            'token'         => 'required',
            'id'            => 'required|integer',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
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

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
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

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        \Cache::tags( 'devices' )->forget( 'devices.user.' . \Auth::user()->id . '.contacts' );

        $this->addLog( 'Очистил кеш' );

        return $this->success( 'OK' );

    }

}