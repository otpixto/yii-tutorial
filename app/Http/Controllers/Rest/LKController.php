<?php

namespace App\Http\Controllers\Rest;

use App\Classes\LK;
use App\Classes\SegmentChilds;
use App\Jobs\SendSms;
use App\Models\Building;
use App\Models\File;
use App\Models\Ticket;
use App\Models\Type;
use App\Models\Work;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Storage;

class LKController extends BaseController
{

    protected $credentials = [
        'phone',
        'password'
    ];

    public function __construct ( Request $request )
    {
        $this->setLogs( storage_path( 'logs/rest_lk.log' ) );
        parent::__construct( $request );
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
                'phone' => 'required|digits:10',
                'password' => 'required|min:5|max:50',
            ] );

            if ( $validation->fails() )
            {
                return $this->error( $validation->errors()
                    ->first() );
            }

            if ( ! $this->checkAuth( $request, $error, $httpCode ) )
            {
                return $this->error( $error, $httpCode );
            }

            if ( ! $this->checkSmsAuth( $request, $error, $httpCode ) )
            {
                return $this->error( $error, $httpCode );
            }

            $user = \Auth::user();

            $this->addLog( 'Авторизовался' );

            $token = $this->providerToken->token;

            return $this->success( [
                'id' => $user->id,
                'fullname' => $user->getName(),
                'token' => $this->sms_confirm ? null : $token,
                'sms_confirm' => (bool) $this->sms_confirm,
            ] );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function addresses ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

            $validation = \Validator::make( $request->all(), [
                'term' => 'required|min:3',
            ] );

            if ( $validation->fails() )
            {
                return $this->error( $validation->errors()
                    ->first() );
            }

            $term = trim( $request->get( 'term' ) );

            $term = '%' . str_replace( ' ', '%', $term ) . '%';

            $segmentIds = [];
            if ( \Auth::user()->customer && \Auth::user()->customer->actualBuilding )
            {
                $actualBuilding = \Auth::user()->customer->actualBuilding;
                $segments = $actualBuilding->getSegments( true );
                if ( $segments->count() )
                {
                    $segment = $segments->first();
                    $segmentChilds = new SegmentChilds( $segment );
                    $segmentIds = $segmentChilds->ids;
                }
            }

            $buildings = Building
                ::where( 'provider_id', '=', \Auth::user()->provider_id )
                ->where( 'name', 'like', $term )
                ->orderBy( 'name' )
                ->take( 30 );

            if ( count( $segmentIds ) )
            {
                $buildings
                    ->whereIn( 'segment_id', $segmentIds );
            }

            $buildings = $buildings->get();

            $this->addLog( 'Запросил список адресов' );

            $response = LK::buildingsInfo( $buildings );

            return $this->success( $response );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function types ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

            $types = Type
                ::where( 'provider_id', '=', \Auth::user()->provider_id )
                ->where( 'lk', '=', 1 )
                ->select(
                    'id',
                    'name AS text'
                )
                ->orderBy( 'name' )
                ->get();

            $this->addLog( 'Запросил список классификатора' );

            return $this->success( $types );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function profile ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

            $user = \Auth::user();
            $customer = $user->customer;

            $response = [
                'user_id' => $user->id,
                'firstname' => $user->firstname,
                'middlename' => $user->middlename,
                'lastname' => $user->lastname,
                'phone' => $user->phone,
                'email' => $user->email,
                'customer_id' => null,
                'building_id' => null,
                'building_name' => null,
                'flat' => null,
            ];

            if ( $customer )
            {
                $response[ 'customer_id' ] = $customer->id;
                if ( $customer->actualBuilding )
                {
                    $response[ 'building_id' ] = $customer->actualBuilding->id;
                    $response[ 'building_name' ] = $customer->actualBuilding->name;
                } else
                {
                    $response[ 'building_id' ] = null;
                    $response[ 'building_name' ] = null;
                }
                $response[ 'flat' ] = $customer->actual_flat;
                $response[ 'buildings' ] = [];
                foreach ( $customer->buildings as $building )
                {
                    $response[ 'buildings' ][] = [
                        'id' => $building->id,
                        'text' => $building->name,
                    ];
                }
            }

            $this->addLog( 'Запросил данные профиля' );

            return $this->success( $response );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function statuses ( Request $request ) : Response
    {
        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }
        try
        {
            $this->addLog( 'Запросил список статусов' );
            $statuses = Ticket::$statuses;
            unset( $statuses[ 'draft' ] );
            return $this->success( $statuses );
        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }
    }

    public function addressAdd ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

            $validation = \Validator::make( $request->all(), [
                'building_id' => 'required|integer',
            ] );

            if ( $validation->fails() )
            {
                return $this->error( $validation->errors()
                    ->first() );
            }

            $user = \Auth::user();
            $customer = $user->customer;

            if ( ! $customer )
            {
                return $this->error( 'Заявитель не найден' );
            }

            $building = Building::find( $request->get( 'building_id' ) );
            if ( ! $building )
            {
                return $this->error( 'Адрес не найден' );
            }

            if ( $customer->buildings->find( $building->id ) )
            {
                return $this->error( 'Адрес уже добавлен' );
            }

            $customer->buildings()
                ->attach( $building->id );

            $this->addLog( 'Добавил адрес ' . $building->name );

            return $this->success( [ 'message' => 'OK' ] );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function addressDel ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

            $validation = \Validator::make( $request->all(), [
                'building_id' => 'required|integer',
            ] );

            if ( $validation->fails() )
            {
                return $this->error( $validation->errors()
                    ->first() );
            }

            $user = \Auth::user();
            $customer = $user->customer;

            if ( ! $customer )
            {
                return $this->error( 'Заявитель не найден' );
            }

            $building = Building::find( $request->get( 'building_id' ) );
            if ( ! $building )
            {
                return $this->error( 'Адрес не найден' );
            }

            if ( ! $customer->buildings->find( $building->id ) )
            {
                return $this->error( 'Адрес еще не добавлен' );
            }

            $customer->buildings()
                ->detach( $building->id );

            $this->addLog( 'Убрал адрес ' . $building->name );

            return $this->success( [ 'message' => 'OK' ] );

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

            $validation = \Validator::make( $request->all(), [
                'page' => 'nullable|integer|min:1',
                'ticket_id' => 'nullable|integer',
                'date_from' => 'nullable|date|date_format:Y-m-d',
                'date_to' => 'nullable|date|date_format:Y-m-d|after_or_equal:date_from',
                'building_id' => 'nullable|integer',
                'type_id' => 'nullable|integer',
                'status_code' => 'nullable|string',
            ] );

            if ( $validation->fails() )
            {
                return $this->error( $validation->errors()
                    ->first() );
            }

            $tickets = Ticket
                ::where( function ( $q )
                {
                    return $q
                        ->where( 'author_id', '=', \Auth::user()->id )
                        ->orWhereHas( 'customer', function ( $customer )
                        {
                            return $customer
                                ->where( 'phone', '=', \Auth::user()->phone );
                        } );
                } )
                ->whereHas( 'building' )
                ->where( 'status_code', '!=', 'draft' );

            if ( $request->get( 'ticket_id' ) )
            {
                $tickets
                    ->where( 'id', '=', $request->get( 'ticket_id' ) );
            }

            if ( $request->get( 'date_from' ) )
            {
                $tickets
                    ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $request->get( 'date_from' ) )
                        ->toDateTimeString() ] );
            }

            if ( $request->get( 'date_to' ) )
            {
                $tickets
                    ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $request->get( 'date_to' ) )
                        ->toDateTimeString() ] );
            }

            if ( $request->get( 'building_id' ) )
            {
                $tickets
                    ->where( 'building_id', '=', $request->get( 'building_id' ) );
            }

            if ( $request->get( 'type_id' ) )
            {
                $type_id = $request->get( 'type_id' );
                $tickets
                    ->whereHas( 'type', function ( $type ) use ( $type_id )
                    {
                        return $type
                            ->where( 'id', '=', $type_id )
                            ->orWhere( 'parent_id', '=', $type_id );
                    } );
            }

            if ( $request->get( 'status_code' ) )
            {
                $tickets
                    ->where( 'status_code', '=', $request->get( 'status_code' ) );
            }

            $tickets = $tickets
                ->orderBy( 'id', 'desc' )
                ->paginate( config( 'pagination.per_page' ) );

            $this->addLog( 'Запросил список заявок' );

            $response = LK::ticketsInfo( $tickets, $this->providerToken->token, $request->get( 'ticket_id' ) ? true : false );

            return $this->success( $response );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function works ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

            $validation = \Validator::make( $request->all(), [
                'page' => 'nullable|integer|min:1',
                'work_id' => 'nullable|integer',
                'date_from' => 'nullable|date|date_format:Y-m-d',
                'date_to' => 'nullable|date|date_format:Y-m-d|after:date_from',
                'building_id' => 'nullable|integer',
            ] );

            if ( $validation->fails() )
            {
                return $this->error( $validation->errors()
                    ->first() );
            }

            $works = Work
                ::current();

            if ( $request->get( 'work_id' ) )
            {
                $works
                    ->where( 'id', '=', $request->get( 'work_id' ) );
            }

            if ( $request->get( 'date_from' ) )
            {
                $works
                    ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $request->get( 'date_from' ) )
                        ->toDateTimeString() ] );
            }

            if ( $request->get( 'date_to' ) )
            {
                $works
                    ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $request->get( 'date_to' ) )
                        ->toDateTimeString() ] );
            }

            if ( $request->get( 'building_id' ) )
            {
                $works
                    ->whereHas( 'buildings', function ( $buildings ) use ( $request )
                    {
                        $ids = [];
                        if ( \Auth::user()->customer )
                        {
                            $ids = \Auth::user()->customer->buildings->pluck( 'id' )
                                ->toArray();
                            if ( \Auth::user()->customer->actualBuilding )
                            {
                                $ids[] = \Auth::user()->customer->actualBuilding->id;
                            }
                        }
                        return $buildings
                            ->where( 'lon', '!=', - 1 )
                            ->where( 'lat', '!=', - 1 )
                            ->whereIn( Building::$_table . '.id', $ids )
                            ->where( Building::$_table . '.id', '=', $request->get( 'building_id' ) );
                    } );
            } else
            {
                $works
                    ->whereHas( 'buildings', function ( $buildings ) use ( $request )
                    {
                        $ids = [];
                        if ( \Auth::user()->customer )
                        {
                            $ids = \Auth::user()->customer->buildings->pluck( 'id' )
                                ->toArray();
                            if ( \Auth::user()->customer->actualBuilding )
                            {
                                $ids[] = \Auth::user()->customer->actualBuilding->id;
                            }
                        }
                        return $buildings
                            ->where( 'lon', '!=', - 1 )
                            ->where( 'lat', '!=', - 1 )
                            ->whereIn( Building::$_table . '.id', $ids );
                    } );
            }

            $works = $works
                ->orderBy( 'id', 'desc' )
                ->paginate( config( 'pagination.per_page' ) );

            $this->addLog( 'Запросил список отключений' );

            $response = LK::worksInfo( $works );

            return $this->success( $response );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function create ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

            $validation = \Validator::make( $request->all(), [
                'type_id' => 'required|integer',
                'building_id' => 'required|integer',
                'flat' => 'required',
                'text' => 'required|max:1000',
                'phone' => 'nullable|digits:10',
                'time_from' => 'nullable|required_with:time_to|date_format:H:i',
                'time_to' => 'nullable|required_with:time_from|date_format:H:i|after:time_from',
                'files' => 'nullable|array',
                'files.*' => 'file|mimes:jpg,jpeg,png,bmp,webp|max:2048',
            ] );

            if ( $validation->fails() )
            {
                return $this->error( $validation->errors()
                    ->first() );
            }

            \DB::beginTransaction();

            $ticket = Ticket::create( [
                'type_id' => $request->get( 'type_id' ),
                'building_id' => $request->get( 'building_id' ),
                'flat' => $request->get( 'flat' ),
                'text' => $request->get( 'text' ),
                'time_from' => $request->get( 'time_from' ),
                'time_to' => $request->get( 'time_to' ),
                'provider_id' => \Auth::user()->provider_id,
                'phone' => \Auth::user()->phone,
                'phone2' => $request->get( 'phone' ),
                'firstname' => \Auth::user()->firstname,
                'middlename' => \Auth::user()->middlename,
                'lastname' => \Auth::user()->lastname,
                'from_lk' => 1,
            ] );

            $res = $ticket->changeStatus( 'moderate', true );
            if ( $res instanceof MessageBag )
            {
                return $this->error( $res->first() );
            }

            foreach ( $request->file( 'files', [] ) as $_file )
            {
                $path = Storage::putFile( 'files', $_file );
                if ( ! $path )
                {
                    return $this->error( trans( 'lk.file' ) );
                }
                $file = File::create( [
                    'model_id' => $ticket->id,
                    'model_name' => get_class( $ticket ),
                    'path' => $path,
                    'name' => $_file->getClientOriginalName()
                ] );
                if ( $file instanceof MessageBag )
                {
                    Storage::delete( $path );
                    return $this->error( $file->first() );
                }
                $file->save();
                $ticket->addLog( 'Загрузил файл "' . $file->name . '"' );
            }

            $this->addLog( 'Создал заявку' );

            \DB::commit();

            \Cache::tags( 'tickets_counts' )
                ->flush();

            $response = LK::ticketInfo( $ticket );

            return $this->success( $response );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function changeEmail ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

            $user = \Auth::user();

            $validation = \Validator::make( $request->all(), [
                'email' => 'required|email|unique:users,email,' . $user->id,
            ] );

            if ( $validation->fails() )
            {
                return $this->error( $validation->errors()
                    ->first() );
            }

            $res = $user->edit( [
                'email' => $request->get( 'email' )
            ] );
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

    public function rate ( Request $request ) : Response
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

            $validation = \Validator::make( $request->all(), [
                'ticket_id' => 'required|integer',
                'rate' => 'required|integer|min:1|max:5',
                'rate_comment' => 'required_if:rate,1|required_if:rate,2|required_if:rate,3|max:1000',
                'files' => 'nullable|array',
                'files.*' => 'file|mimes:jpg,jpeg,png,bmp,webp|max:2048',
            ] );

            if ( $validation->fails() )
            {
                return $this->error( $validation->errors()
                    ->first() );
            }

            \DB::beginTransaction();

            $ticket = Ticket::find( $request->get( 'ticket_id' ) );
            if ( ! $ticket )
            {
                return $this->error( 'Заявка не найдена', 404 );
            }

            if ( ! $ticket->managements->count() )
            {
                return $this->error( 'Невозможно поставить оценку заявке' );
            }

            $flag = false;
            foreach ( $ticket->managements as $ticketManagement )
            {
                if ( $ticketManagement->canRate() )
                {
                    $ticketManagement->rate = $request->get( 'rate' );
                    $ticketManagement->rate_comment = $request->get( 'rate_comment' );
                    $ticketManagement->save();
                    $res = $ticketManagement->changeStatus( 'closed_with_confirm', true );
                    if ( $res instanceof MessageBag )
                    {
                        return $this->error( $res->first() );
                    }
                    $flag = true;
                }
            }

            if ( ! $flag )
            {
                return $this->error( 'Невозможно поставить оценку заявке' );
            }

            foreach ( $request->file( 'files', [] ) as $_file )
            {
                $path = Storage::putFile( 'files', $_file );
                if ( ! $path )
                {
                    return $this->error( trans( 'lk.file' ) );
                }
                $file = File::create( [
                    'model_id' => $ticket->id,
                    'model_name' => get_class( $ticket ),
                    'path' => $path,
                    'name' => $_file->getClientOriginalName()
                ] );
                if ( $file instanceof MessageBag )
                {
                    Storage::delete( $path );
                    return $this->error( $file->first() );
                }
                $file->save();
                $ticket->addLog( 'Загрузил файл "' . $file->name . '"' );
            }

            $this->addLog( 'Поставил оценку ' . $request->get( 'rate' ) );

            \DB::commit();

            \Cache::tags( 'tickets_counts' )
                ->flush();

            return $this->success( [ 'message' => 'OK' ] );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function recovery ( Request $request ) : Response
    {

        if ( ! $this->checkProviderKey( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        try
        {

            $validation = \Validator::make( $request->all(), [
                'phone' => 'required|digits:10',
            ] );

            if ( $validation->fails() )
            {
                return $this->error( $validation->errors()
                    ->first() );
            }

            $phone = $request->get( 'phone' );

            $user = User
                ::where( 'phone', '=', $phone )
                ->first();

            if ( ! $user )
            {
                return $this->error( 'Пользователь не найден', 404 );
            }

            if ( ! $this->checkSmsConfirm( $request, $error, $httpCode ) )
            {
                return $this->error( $error, $httpCode );
            }

            if ( $request->get( 'sms_code' ) )
            {
                $password = str_random( 5 );
                $message = 'Ваш новый пароль: ' . $password;
                $this->dispatch( new SendSms( $phone, $message ) );
                $user->changePass( $password );
            }

            return $this->success( [ 'message' => 'OK' ] );

        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }

    }

    public function unsubscribe ()
    {
        try
        {
            $user = \Auth::user();

            $user->email_subscribed = 0;

            $user->save();

            return $this->success( [ 'message' => 'OK' ] );
        }
        catch ( \Exception $e )
        {
            return $this->error( 'Внутренняя ошибка системы!', 500 );
        }
    }

}