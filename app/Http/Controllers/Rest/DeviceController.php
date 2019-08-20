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
        try
        {
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
                'prefix'        => $user->prefix,
                'fullname'      => $user->getName(),
                'token'         => $providerToken->token
            ]);
        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }
    }

    public function tickets ( Request $request ) : Response
    {
        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }
        try
        {

            if ( ! \Auth::user()->can( 'rest.tickets.show' ) )
            {
                return $this->error( trans('device.denied' ), 403 );
            }

            $validation = \Validator::make( $request->all(), [
                'ticket_id'           => 'nullable|integer',
                'date_from'           => 'nullable|date|date_format:Y-m-d',
                'date_to'             => 'nullable|date|date_format:Y-m-d',
                'building_id'         => 'nullable|integer',
                'statuses'            => 'nullable|array',
            ]);

            if ( $validation->fails() )
            {
                return $this->error( $validation->errors()->first() );
            }

            $tickets = TicketManagement
                ::forme()
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

            if ( $request->get( 'statuses' ) )
            {
                $tickets
                    ->whereIn( 'status_code', $request->get( 'statuses' ) );
            }

            $tickets = $tickets
                ->orderBy( 'id', 'desc' )
                ->paginate( config( 'pagination.per_page' ) );

            $tickets = Devices::ticketsInfo( $tickets, $request->get( 'ticket_id' ) ? true : false );

            $this->addLog( 'Запросил список заявок' );

            return $this->success( $tickets );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }
    }

    public function contacts ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

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
                foreach ( \Auth::user()->managements as $management )
                {
                    $executors = $management
                        ->executors()
                        ->whereNotNull( 'phone' )
                        ->get();
                    foreach ( $executors as $executor )
                    {
                        $contacts[] = [
                            'fullname'      => $executor->name,
                            'phone'         => $executor->phone
                        ];
                    }
                }
                \Cache::tags( 'devices' )->put( 'devices.user.' . \Auth::user()->id . '.contacts', $contacts, 15 );
            }

            $this->addLog( 'Запросил список контактов' );

            return $this->success( $contacts );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function position ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

            if ( ! \Auth::user()->can( 'rest.position' ) )
            {
                return $this->error( trans('device.denied' ), 403 );
            }

            $validation = \Validator::make( $request->all(), [
                'coors'                 => 'required|array',
                'coors.*.timestamp'     => 'required|integer',
                'coors.*.lon'           => 'required|numeric',
                'coors.*.lat'           => 'required|numeric',
            ]);

            if ( $validation->fails() )
            {
                return $this->error( $validation->errors()->first() );
            }

            foreach ( $request->get( 'coors', [] ) as $coors )
            {
                $res = \Auth::user()->setPosition(
                    $coors[ 'lon' ],
                    $coors[ 'lat' ],
                    Carbon::createFromTimestamp( $coors[ 'timestamp' ] )
                );
                if ( $res instanceof MessageBag )
                {
                    return $this->error( $res->first() );
                }
            }

            $this->addLog( 'Сообщил о своем местоположении' );

            return $this->success( [ 'message' => 'OK' ] );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function complete ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
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

            if ( ! \Auth::user()->can( 'rest.tickets.edit' ) )
            {
                return $this->error( trans('device.denied' ), 403 );
            }

            $ticketManagement = TicketManagement
                ::forme()
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

            return $this->success( [ 'message' => 'OK' ] );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function inProcess ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

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
                ::forme()
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

            return $this->success( [ 'message' => 'OK' ] );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function getPhone ( Request $request ) : Response
    {

        try
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
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }
	
	public function call ( Request $request ) : Response
	{

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

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
                ::forme()
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

            $asterisk = $ticketManagement->ticket->provider->getAsterisk();
            $rest_curl_url = config( 'rest.curl_url' ) . '/ticket-call?ticket_call_id=' . (int) $ticketCall->id;
            if ( ! $asterisk->originate( $number_from, $number_to, $number_from, $rest_curl_url ) )
            {
                return $this->error( $asterisk->last_result );
            }

            \DB::commit();

            return $this->success( [ 'message' => 'OK' ] );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }
	
	}

    public function calls ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

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
                ::forme()
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
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function comment ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

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
                ::forme()
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

            return $this->success( [ 'message' => 'OK' ] );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function clearCache ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {
            \Cache::tags( 'devices' )->forget( 'devices.user.' . \Auth::user()->id . '.contacts' );
            $this->addLog( 'Очистил кеш' );
            return $this->success( [ 'message' => 'OK' ] );
        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

}