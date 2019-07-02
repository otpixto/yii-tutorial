<?php

namespace App\Http\Controllers\Operator;

use App\Classes\SegmentChilds;
use App\Classes\Title;
use App\Jobs\SendSms;
use App\Jobs\SendStream;
use App\Models\Building;
use App\Models\Customer;
use App\Models\Executor;
use App\Models\Management;
use App\Models\Provider;
use App\Models\Segment;
use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Models\Type;
use App\Models\Vendor;
use App\Models\Work;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Ramsey\Uuid\Uuid;

class TicketsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Реестр заявок' );
    }

    public function index ( Request $request )
    {

        if ( $request->ajax() )
        {

            $ticketManagements = TicketManagement
                ::mine()
                ->search( $request )
                ->with(
                    'ticket',
                    'ticket.author',
                    'ticket.type',
                    'ticket.type.parent',
                    'ticket.building',
                    'ticket.building.buildingType',
                    'management',
                    'management.parent',
                    'executor'
                )
                ->paginate( config( 'pagination.per_page' ) )
                ->appends( $request->all() );

            $this->addLog( 'Просмотрел список заявок (стр.' . $request->get( 'page', 1 ) . ')' );

            return view( 'tickets.parts.list' )
                ->with( 'ticketManagements', $ticketManagements );

        }

		if ( \Auth::user()->can( 'tickets.scheduled' ) )
		{
			if ( \Cache::tags( 'tickets.scheduled.now' )->has( 'tickets.scheduled.now.' . \Auth::user()->id ) )
			{
				$scheduledTicketManagements = \Cache::tags( 'tickets.scheduled.now' )->get( 'tickets.scheduled.now.' . \Auth::user()->id );
			}
			else
			{
				$now = Carbon::now()->toDateTimeString();
				$scheduledTicketManagements = TicketManagement
					::mine()
					->where( 'status_code', '=', 'assigned' )
					->where( 'scheduled_begin', '<=', $now )
					->whereDoesntHave( 'ticket', function ( $ticket ) use ( $now )
					{
						return $ticket
							->whereNotNull( 'postponed_to' )
							->where( 'postponed_to', '>', $now );
					})
					->get();
				\Cache::tags( 'tickets.scheduled.now' )->put( 'tickets.scheduled.now.' . \Auth::user()->id, $scheduledTicketManagements, 15 );
			}
		}
		else
		{
			$scheduledTicketManagements = new Collection();
		}

        /*$counts = TicketManagement
            ::mine()
            ->whereIn( TicketManagement::$_table . '.status_code', [ 'created', 'rejected', 'moderate', 'conflict', 'confirmation_operator', 'confirmation_client' ] )
            ->get();*/

        return view( 'tickets.index' )
            ->with( 'request', $request )
            ->with( 'scheduledTicketManagements', $scheduledTicketManagements );

    }

    public function moderate ( Request $request )
    {

        if ( $request->ajax() )
        {

            $tickets = Ticket
                ::where( 'status_code', '=', 'moderate' )
                ->paginate( config( 'pagination.per_page' ) )
                ->appends( $request->all() );

            $this->addLog( 'Просмотрел список заявок на модерацию (стр.' . $request->get( 'page', 1 ) . ')' );

            return view( 'tickets.parts.moderate_list' )
                ->with( 'tickets', $tickets );

        }

        Title::add( 'Модерация заявок' );

        return view( 'tickets.moderate' )
            ->with( 'request', $request );

    }

    public function moderateShow ( Request $request, $ticket_id )
    {

        $ticket = Ticket::find( $ticket_id );

        if ( ! $ticket || $ticket->status_code != 'moderate' )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }

        //Title::add( 'Добавить заявку' );
        Title::add( 'Заявка #' . $ticket->id . ' от ' . $ticket->created_at->format( 'd.m.Y H:i' ) );

        $types = Type
            ::mine()
            //->where( Type::$_table .'.provider_id', '=', $ticket->provider_id )
            ->orderBy( Type::$_table .'.name' )
            ->pluck( 'name', 'id' );

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( Provider::$_table . '.name' )
            ->pluck( Provider::$_table . '.name', 'id' );

        $vendors = Vendor
            ::orderBy( Vendor::$_table . '.name' )
            ->pluck( Vendor::$_table . '.name', 'id' )
            ->toArray();

        return view( 'tickets.create' )
            ->with( 'types', $types )
            ->with( 'ticket', $ticket )
            ->with( 'providers', $providers )
            ->with( 'vendors', $vendors )
            ->with( 'places', Ticket::$places )
            ->with( 'moderate', 1 );

    }

    public function moderateReject ( Request $request, $id )
    {
        $ticket = Ticket::find( $id );
        if ( ! $ticket || $ticket->status_code != 'moderate' )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Заявка не найдена' ] );
        }
        $res = $ticket->changeStatus( 'rejected_operator', true );
        if ( $res instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withErrors( $res );
        }
        \Cache::tags( 'tickets_counts' )->flush();
        return redirect()
            ->route( 'tickets.moderate' )
            ->with( 'success', 'Заявка отклонена' );
    }

    public function export ( Request $request )
    {

        if ( ! \Auth::user()->can( 'tickets.export' ) )
        {
            return redirect()->back()->withErrors( [ 'Доступ запрещен' ] );
        }

        $ticketManagements = TicketManagement
            ::mine()
            ->search( $request )
            ->get();

        if ( $ticketManagements->count() > 3000 )
        {
            return redirect()->back()->withErrors( [ 'Уточните критерии поиска' ] );
        }

        $data = [];
        $i = 0;
        foreach ( $ticketManagements as $ticketManagement )
        {
            $ticket = $ticketManagement->ticket;
            if ( ! $ticket || $ticket->status_code == 'draft' ) continue;
            $data[ $i ] = [
                '#'                     => $ticket->id,
                'Дата и время'          => $ticket->created_at->format( 'd.m.y H:i' ),
                'Текущий статус'        => $ticket->status_name,
                'Здание'                => $ticket->building->name,
                'Квартира'              => $ticket->flat,
                'Проблемное место'      => $ticket->getPlace(),
                'Классификатор'         => $ticket->type->name,
                'Текст обращения'       => $ticket->text,
                'ФИО заявителя'         => $ticket->getName(),
                'Телефон(ы) заявителя'  => $ticket->getPhones(),
            ];
            if ( \Auth::user()->can( 'tickets.field_opeator' ) )
            {
                $data[ $i ][ 'Оператор' ] = $ticket->author->getName();
            }
            if ( \Auth::user()->can( 'tickets.field_management' ) )
            {
                $data[ $i ][ 'Служба эксплуатации' ] = $ticketManagement->management->name;
                if ( $ticketManagement->executor )
                {
                    $data[ $i ][ 'Исполнитель' ] = $ticketManagement->executor->name;
                }
            }
            $i ++;
        }

        $this->addLog( 'Выгрузил список заявок' );

        \Excel::create( 'ЗАЯВКИ', function ( $excel ) use ( $data )
        {
            $excel->sheet( 'ЗАЯВКИ', function ( $sheet ) use ( $data )
            {
                $sheet->fromArray( $data );
            });
        })->export( 'xls' );

        die;

    }

    public function searchForm ( Request $request )
    {

        if ( ! \Auth::user()->can( 'tickets.search' ) )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Доступ запрещен' );
        }

        $types = [];
        $managements = [];
        $operators = [];
        $statuses = [];

        if ( \Auth::user()->can( 'tickets.search' ) )
        {

            if ( ! empty( $request->get( 'statuses' ) ) )
            {
                $statuses = explode( ',', $request->get( 'statuses' ) );
            }

            if ( ! empty( $request->get( 'types' ) ) )
            {
                $types = explode( ',', $request->get( 'types' ) );
            }

            if ( ! empty( $request->get( 'operators' ) ) )
            {
                $operators = explode( ',', $request->get( 'operators' ) );
            }

            if ( ! empty( $request->get( 'managements' ) ) )
            {
                $managements = explode( ',', $request->get( 'managements' ) );
            }

        }

        if ( ! empty( $request->get( 'segment_id' ) ) )
        {
            $segment = Segment::find( $request->get( 'segment_id' ) );
        }

        if ( ! empty( $request->get( 'building_id' ) ) )
        {
            $building = Building::where( 'id', $request->get( 'building_id' ) )->pluck( 'name', 'id' );
        }

        if ( ! empty( $request->get( 'actual_building_id' ) ) )
        {
            $actual_building = Building::where( 'id', $request->get( 'actual_building_id' ) )->pluck( 'name', 'id' );
        }

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( Provider::$_table . '.name' )
            ->pluck( Provider::$_table . '.name', Provider::$_table . '.id' );

        $vendors = Vendor
            ::orderBy( Vendor::$_table . '.name' )
            ->pluck( Vendor::$_table . '.name', Vendor::$_table . '.id' )
            ->toArray();

        $availableStatuses = \Auth::user()->getAvailableStatuses( 'show', true, true );
        $res = Type
            ::mine()
            ->where( 'provider_id', '=', Provider::getCurrent()->id ?? null )
            ->whereHas( 'parent' )
            ->with( 'parent' )
            ->get()
            ->sortBy( 'name' );
        $availableTypes = [];
        foreach ( $res as $r )
        {
            $availableTypes[ $r->parent->name ][ $r->id ] = $r->name;
        }

        if ( \Auth::user()->can( 'tickets.field_operator' ) )
        {
            if ( \Cache::tags( [ 'users', 'ticket' ] )->has( 'operators' ) )
            {
                $availableOperators = \Cache::tags( [ 'users', 'ticket' ] )->get( 'operators' );
            }
            else
            {
                $res = Ticket
                    ::select( 'author_id' )
                    ->whereHas( 'author' )
                    ->distinct( 'author_id' )
                    ->get();
                $availableOperators = [];
                foreach ( $res as $r )
                {
                    $availableOperators[ $r->author_id ] = $r->author->getName();
                }
                asort( $availableOperators );
                \Cache::tags( [ 'users', 'ticket' ] )->put( 'operators', $availableOperators, \Config::get( 'cache.time' ) );
            }
        }

        if ( \Auth::user()->can( 'tickets.field_management' ) )
        {
            $res = Management
                ::mine()
                ->with( 'parent' )
                ->get()
                ->sortBy( 'name' );
            $availableManagements = [];
            foreach ( $res as $r )
            {
                $availableManagements[ $r->parent->name ?? 'Разное' ][ $r->id ] = $r->name;
            }
        }

        /*if ( ! count( $types ) )
        {
            foreach ( $availableTypes as $category )
            {
                $types = array_merge( $types, array_keys( $category ) );
            }
        }*/

        if ( ! count( $statuses ) )
        {
            $statuses = array_keys( $availableStatuses );
        }

        return view( 'tickets.parts.search' )
            ->with( 'availableTypes', $availableTypes ?? [] )
            ->with( 'availableStatuses', $availableStatuses ?? [] )
            ->with( 'availableOperators', $availableOperators ?? [] )
            ->with( 'availableManagements', $availableManagements ?? [] )
            ->with( 'types', $types ?? [] )
            ->with( 'managements', $managements ?? [] )
            ->with( 'operators', $operators ?? [] )
            ->with( 'providers', $providers ?? [] )
            ->with( 'vendors', $vendors ?? [] )
            ->with( 'building', $building ?? [] )
            ->with( 'segment', $segment ?? [] )
            ->with( 'actual_building', $actual_building ?? [] )
            ->with( 'statuses', $statuses ?? [] );

    }

    public function customersTickets ( Request $request, $id )
    {

        $ticket = Ticket::find( $id );
        if ( ! $ticket )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Произошла ошибка. Заявка не найдена' );
        }

        $tickets = $ticket
            ->customerTickets()
            ->whereHas( 'managements', function ( $managements )
            {
                return $managements
                    ->mine();
            })
            ->orderBy( 'id', 'desc' )
            ->paginate( config( 'pagination.per_page' ) );

        $ticket->addLog( 'Просмотрел список заявок заявителя' );

        return view( 'tickets.tabs.mini_table' )
            ->with( 'tickets', $tickets )
            ->with( 'link', route( 'tickets.index', [ 'phone' => $ticket->phone ] ) );

    }

    public function addressTickets ( Request $request, $id )
    {

        $ticket = Ticket::find( $id );
        if ( ! $ticket )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Произошла ошибка. Заявка не найдена' );
        }

        $tickets = $ticket
            ->whereHas( 'managements', function ( $managements )
            {
                return $managements
                    ->mine();
            })
            ->where( 'building_id', '=', $ticket->building_id )
            ->where( 'flat', '=', $ticket->flat )
            ->orderBy( 'id', 'desc' )
            ->paginate( config( 'pagination.per_page' ) );

        $ticket->addLog( 'Просмотрел список заявок, оформленных на тот же адрес' );

        return view( 'tickets.tabs.mini_table' )
            ->with( 'tickets', $tickets )
            ->with( 'link', route( 'tickets.index', [ 'building_id' => $ticket->building_id, 'flat' => $ticket->flat ] ) );

    }

    public function neighborsTickets ( Request $request, $id )
    {

        $ticket = Ticket::find( $id );
        if ( ! $ticket )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Произошла ошибка. Заявка не найдена' );
        }

        $tickets = $ticket
            ->neighborsTickets()
            ->whereHas( 'managements', function ( $managements )
            {
                return $managements
                    ->mine();
            })
            ->where( 'phone', '!=', $ticket->phone )
            ->where( 'flat', '!=', $ticket->flat )
            ->orderBy( 'id', 'desc' )
            ->paginate( config( 'pagination.per_page' ) );

        $ticket->addLog( 'Просмотрел список заявок соседей' );

        return view( 'tickets.tabs.mini_table' )
            ->with( 'tickets', $tickets )
            ->with( 'link', route( 'tickets.index', [ 'building_id' => $ticket->building_id ] ) );

    }

    public function services ( Request $request, $id )
    {

        $ticketManagement = TicketManagement::find( $id );
        if ( ! $ticketManagement )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Произошла ошибка. Заявка не найдена' );
        }

        $ticketManagement->addLog( 'Просмотрел список выполненных работ' );

        return view( 'tickets.tabs.services' )
            ->with( 'ticketManagement', $ticketManagement );

    }

    public function works ( Request $request, $id )
    {

        $ticket = Ticket::find( $id );
        if ( ! $ticket )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Произошла ошибка. Заявка не найдена' );
        }

        $category_id = $ticket->type->parent_id ?: $ticket->type->id;

        $works = Work
            ::mine()
            ->current()
            ->whereHas( 'buildings', function ( $buildings ) use ( $ticket )
            {
                return $buildings
                    ->where( Building::$_table . '.id', '=', $ticket->building_id );
            })
            ->where( 'category_id', '=', $category_id )
            ->orderBy( 'id', 'desc' )
            ->paginate( config( 'pagination.per_page' ) );

        $ticket->addLog( 'Просмотрел список отключений' );

        return view( 'tickets.tabs.works' )
            ->with( 'works', $works )
            ->with( 'link', route( 'works.index', [ 'building_id' => $ticket->building_id, 'category_id' => $category_id ] ) );

    }

    public function line ( Request $request, $id )
    {

        $field_operator = \Auth::user()->can( 'tickets.field_operator' );
        $field_management = \Auth::user()->can( 'tickets.field_management' );

        $ticketManagement = TicketManagement
            ::mine()
            ->where( 'id', '=', $id )
            ->with(
                'comments',
                'ticket',
                'management'
            )
            ->first();

        if ( ! $ticketManagement ) return;

        $hide = $ticketManagement->ticket->author_id == \Auth::user()->id ? false : $request->get( 'hide', false );

        return view( 'tickets.parts.line' )
            ->with( 'ticketManagement', $ticketManagement )
            ->with( 'ticket', $ticketManagement->ticket )
            ->with( 'field_operator', $field_operator )
            ->with( 'field_management', $field_management )
            ->with( 'hide', $hide )
            ->with( 'hideComments', $request->get( 'hideComments', false ) );

    }

    public function comments ( Request $request, $id = null )
    {

        if ( $id )
        {
            $ticket = Ticket
                ::whereHas( 'managements', function ( $managements )
                {
                    return $managements
                        ->mine();
                })
                ->where( 'id', '=', $id )
                ->first();
            return view( 'parts.comments' )
                ->with( 'origin', $ticket )
                ->with( 'comments', $ticket->comments );
        }
        else if ( is_array( $request->get( 'ids' ) ) && count( $request->get( 'ids' ) ) )
        {
            $tickets = Ticket
                ::whereHas( 'managements', function ( $managements )
                {
                    return $managements
                        ->mine();
                })
                ->whereIn( 'id', $request->get( 'ids' ) )
                ->with( 'comments' )
                ->get();
            $response = [];
            foreach ( $tickets as $ticket )
            {
                if ( $ticket->comments->count() )
                {
                    $response[ $ticket->id ] = view( 'parts.comments' )
                        ->with( 'origin', $ticket )
                        ->with( 'comments', $ticket->comments )
                        ->render();
                }
            }
            return $response;
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ( Request $request )
    {

        $emergency = $request->get( 'emergency', 0 );
        $ticket = Ticket::create( [], $emergency );

        if ( $ticket instanceof MessageBag )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( $ticket );
        }

        //Title::add( 'Добавить заявку' );
        Title::add( 'Заявка #' . $ticket->id . ' от ' . $ticket->created_at->format( 'd.m.Y H:i' ) );

        $types = Type
            ::mine()
            ->orderBy( Type::$_table .'.name' );

        if ( $ticket->provider_id )
        {
            $types
                ->where( Type::$_table .'.provider_id', '=', $ticket->provider_id );
        }

        $types = $types->pluck( 'name', 'id' );

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( Provider::$_table . '.name' )
            ->pluck( Provider::$_table . '.name', 'id' );

        $vendors = Vendor
            ::orderBy( Vendor::$_table . '.name' )
            ->pluck( Vendor::$_table . '.name', 'id' )
            ->toArray();

        return view( 'tickets.create' )
            ->with( 'types', $types )
            ->with( 'ticket', $ticket )
            ->with( 'providers', $providers )
            ->with( 'vendors', $vendors )
            ->with( 'emergency', $emergency )
            ->with( 'places', Ticket::$places );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store ( Request $request )
    {

        try
        {

            $rules = [
                'provider_id'               => 'nullable|integer',
                'vendor_id'                 => 'nullable|integer',
                'vendor_date'               => 'nullable|date',
                'type_id'                   => 'required|integer',
                'ticket_id'                 => 'required|integer',
                'building_id'               => 'required|integer',
                'flat'                      => 'nullable',
                'actual_address_id'         => 'nullable|integer',
                'actual_flat'               => 'nullable',
                'place_id'                  => 'required|integer',
                'emergency'                 => 'boolean',
                'urgently'                  => 'boolean',
                'dobrodel'                  => 'boolean',
                'phone'                     => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
                'phone2'                    => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
                'firstname'                 => 'required',
                'middlename'                => 'nullable',
                'lastname'                  => 'nullable',
                'customer_id'               => 'nullable|integer',
                'text'                      => 'required',
                'managements'               => 'required|array',
                'create_another'            => 'nullable|boolean',
                'create_user'               => 'nullable|boolean',
            ];

            $this->validate( $request, $rules );

            if ( ! isset( Ticket::$places[ $request->get( 'place_id' ) ] ) )
            {
                return redirect()->back()->withErrors( [ 'Некорректное проблемное место' ] );
            }

            \DB::beginTransaction();

            $ticket = Ticket
                ::where( function ( $q )
                {
                    return $q
                        ->where( function ( $q2 )
                        {
                            return $q2
                                ->where( 'status_code', '=', 'draft' )
                                ->where( 'author_id', '=', \Auth::user()->id );
                        })
                        ->orWhere( 'status_code', '=', 'moderate' );
                })
                ->find( $request->get( 'ticket_id' ) );

            if ( ! $ticket )
            {
                return redirect()
                    ->back()
                    ->withErrors( [ 'Невозможно создать заявку' ] );
            }

            $ticket->created_at = Carbon::now()->toDateTimeString();
            $ticket->author_id = \Auth::user()->id;
            $res = $ticket->edit( $request->all() );

            if ( $res instanceof MessageBag )
            {
                return redirect()->back()->withErrors( $res );
            }

            $customer = $ticket->customer()->mine()->first();

            if ( $customer )
            {
                $customer->edit( $request->all() );
                $ticket->customer_id = $customer->id;
            }
            else
            {
                $customer = Customer::create( $request->all() );
                $customer->save();
            }

            $status_code = 'no_contract';
            $managements = array_unique( $request->get( 'managements', [] ) );
            $managements_count = 0;

            foreach ( $managements as $management_id )
            {

                $ticketManagement = $ticket->managements()->find( $management_id );
                if ( $ticketManagement ) continue;

                $ticketManagement = TicketManagement::create([
                    'ticket_id'         => $ticket->id,
                    'management_id'     => $management_id,
                ]);

                if ( $ticketManagement instanceof MessageBag )
                {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors( $ticketManagement );
                }

                $ticketManagement->save();

                if ( $ticketManagement->management->has_contract )
                {
                    if ( $request->get( 'create_another' ) )
                    {
                        $status_code = 'transferred';
                    }
                    else
                    {
                        $status_code = 'created';
                    }
                    $res = $ticketManagement->changeStatus( $status_code, true );
                    if ( $res instanceof MessageBag )
                    {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors( $res );
                    }

                    if ( $ticket->type->mosreg_id && $ticket->building->mosreg_id )
                    {
                        if ( $ticketManagement->management->hasMosreg( $mosreg ) )
                        {
                            $res = $mosreg->createTicket([
                                'company_id'            => $mosreg->id,
                                'customer_name'         => $ticket->getName(),
                                'customer_email'        => null,
                                'customer_phone'        => '7' . $ticket->phone,
                                'address_id'            => $ticket->building->mosreg_id,
                                'flat'                  => $ticket->flat,
                                'type_id'               => $ticket->type->mosreg_id,
                                'text'                  => $ticket->text,
                            ]);
                            if ( isset( $res->error ) )
                            {
                                return redirect()
                                    ->back()
                                    ->withErrors( [ $res->error ] );
                            }
                            $ticketManagement->mosreg_id = $res->id;
                            $ticketManagement->mosreg_number = $res->compositeId;
                            $ticketManagement->mosreg_status = $res->status;
                            $ticketManagement->save();
                        }
                    }

                }
                else
                {
                    $res = $ticketManagement->changeStatus( 'no_contract', true );
                    if ( $res instanceof MessageBag )
                    {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors( $res );
                    }
                }

                $this->dispatch( new SendStream( 'create', $ticketManagement ) );

                $managements_count ++;

            }

            $res = $ticket->changeStatus( $status_code, true );

            if ( $res instanceof MessageBag )
            {
                return redirect()->back()
                    ->withInput()
                    ->withErrors( $res );
            }

            if ( $request->get( 'create_user' ) && $ticket->canCreateUser( true ) )
            {

                $password = str_random( 5 );
                $message = 'lk.eds-region.ru. Логин: ' . $ticket->phone . '. Пароль: ' . $password;

                $res = User
                    ::create([
                        'provider_id'                   => $ticket->provider_id,
                        'active'                        => 1,
                        'firstname'                     => $ticket->firstname,
                        'middlename'                    => $ticket->middlename,
                        'lastname'                      => $ticket->lastname,
                        'phone'                         => $ticket->phone,
                        'password'                      => $password,
                    ]);

                if ( $res instanceof MessageBag )
                {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors( $res );
                }

                $this->dispatch( new SendSms( $ticket->phone, $message ) );

            }

            if ( $request->get( 'create_another' ) )
            {

                $redirect = route( 'tickets.create' );

                $anotherTicket = Ticket::create();

                if ( $anotherTicket instanceof MessageBag )
                {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors( $anotherTicket );
                }

                $anotherTicket->fill([
                    'firstname'                     => $ticket->firstname,
                    'middlename'                    => $ticket->middlename,
                    'lastname'                      => $ticket->lastname,
                    'phone'                         => $ticket->phone,
                    'phone2'                        => $ticket->phone2,
                    'actual_building_id'            => $ticket->actual_building_id,
                    'actual_flat'                   => $ticket->actual_flat,
                ]);

                $anotherTicket->call_phone = $ticket->call_phone;
                $anotherTicket->call_id = $ticket->call_id;
                $anotherTicket->save();

            }
            else
            {
                $redirect = route( 'tickets.show', $managements_count == 1 ? $ticketManagement->getTicketNumber() : $ticket->id );
            }

            \DB::commit();

            \Cache::tags( 'tickets_counts' )->flush();

            return redirect()
                ->to( $redirect )
                ->with( 'success', 'Заявка успешно добавлена' );

        }
        catch ( \Exception $e )
        {
            return redirect()->back()->withErrors( [ 'Внутренняя ошибка системы!' ] );
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $ticket_id
     * @param  int  $ticket_management_id
     * @return \Illuminate\Http\Response
     */
    public function show ( Request $request, $ticket_id, $ticket_management_id = null )
    {

        $ticket = Ticket::mine()->find( $ticket_id );
        $ticketManagement = null;

        if ( ! $ticket )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }

        if ( $ticket->status_code == 'draft' )
        {
            if ( $ticket->author_id == \Auth::user()->id )
            {
                return redirect()
                    ->route( 'tickets.create' );
            }
            else
            {
                return redirect()
                    ->route( 'tickets.index' )
                    ->withErrors( [ 'Заявка не найдена' ] );
            }
        }

        $comments = $ticket->comments;

        if ( $ticket_management_id )
        {
            $ticketManagement = $ticket
                ->managements()
                ->mine()
                ->find( $ticket_management_id );
            if ( ! $ticketManagement )
            {
                return redirect()
                    ->route( 'tickets.index' )
                    ->withErrors( [ 'Заявка не найдена' ] );
            }
            $ticketManagement->addLog( 'Просмотрел заявку №' . $ticketManagement->getTicketNumber() );
            Title::add( 'Заявка #' . $ticketManagement->getTicketNumber() . ' от ' . $ticketManagement->ticket->created_at->format( 'd.m.Y H:i' ) );
            $servicesCount = $ticketManagement
                ->services()
                ->count();
        }
        else
        {
            $ticketManagements = $ticket->managements()->mine()->get();
            if ( $ticketManagements->count() == 1 )
            {
                return redirect()
                    ->route( 'tickets.show', $ticketManagements->first()->getTicketNumber() );
            }
            $ticket->addLog( 'Просмотрел заявку №' . $ticket->id );
            Title::add( 'Заявка #' . $ticket->id . ' от ' . $ticket->created_at->format( 'd.m.Y H:i' ) );
            $servicesCount = 0;
        }

        if ( \Auth::user()->can( 'tickets.calls.all' ) || \Auth::user()->can( 'tickets.calls.mine' ) )
        {
            $ticketCalls = $ticket->calls()->actual();
            if ( ! \Auth::user()->can( 'tickets.calls.all' ) )
            {
                $ticketCalls
                    ->mine();
            }
            $ticketCalls = $ticketCalls->get();
        }
        else
        {
            $ticketCalls = new Collection();
        }

        $availableStatuses = [];
        $model_name = get_class( $ticket );
        $model_id = $ticket->id;
        $url = route( 'tickets.status', $ticket->id );
        foreach ( $ticket->getAvailableStatuses( 'edit', true, true ) as $status_code => $status_name )
        {
            $availableStatuses[ $status_code ] = compact( 'status_name', 'model_name', 'model_id', 'url' );
        }

        if ( $ticketManagement )
        {
            $model_name = get_class( $ticketManagement );
            $model_id = $ticketManagement->id;
            $url = route( 'tickets.status', $ticketManagement->getTicketNumber() );
            foreach ( $ticketManagement->getAvailableStatuses( 'edit', true, true ) as $status_code => $status_name )
            {
                $availableStatuses[ $status_code ] = compact( 'status_name', 'model_name', 'model_id', 'url' );
            }
        }

        $worksCount = Work
            ::current()
            ->whereHas( 'buildings', function ( $buildings ) use ( $ticket )
            {
                return $buildings
                    ->where( Building::$_table . '.id', '=', $ticket->building_id );
            })
            ->where( 'category_id', '=', $ticket->type->parent_id ?: $ticket->type->id )
            ->count();

        $neighborsTicketsCount = $ticket
            ->neighborsTickets()
            ->whereHas( 'managements', function ( $managements )
            {
                return $managements
                    ->mine();
            })
            ->where( 'phone', '!=', $ticket->phone )
            ->where( 'flat', '!=', $ticket->flat )
            ->count();

        if ( $ticket->phone )
        {
            $customerTicketsCount = $ticket
                ->customerTickets()
                ->whereHas( 'managements', function ( $managements )
                {
                    return $managements
                        ->mine();
                })
                ->count();
        }

        $addressTicketsCount = $ticket
            ->whereHas( 'managements', function ( $managements )
            {
                return $managements
                    ->mine();
            })
            ->where( 'building_id', '=', $ticket->building_id )
            ->where( 'flat', '=', $ticket->flat )
            ->count();

        $progressData = $ticket->getProgressData();

        if ( $ticketManagement )
        {
            $need_act = $ticketManagement->needAct();
        }
        else
        {
            $need_act = $ticket->needAct();
        }

        return view( $request->ajax() ? 'tickets.parts.info' : 'tickets.show' )
            ->with( 'ticket', $ticket )
            ->with( 'ticketManagement', $ticketManagement ?? null )
            ->with( 'progressData', $progressData )
            ->with( 'availableStatuses', $availableStatuses )
            ->with( 'ticketCalls', $ticketCalls )
            ->with( 'comments', $comments )
            ->with( 'worksCount', $worksCount )
            ->with( 'servicesCount', $servicesCount )
            ->with( 'neighborsTicketsCount', $neighborsTicketsCount )
            ->with( 'addressTicketsCount', $addressTicketsCount )
            ->with( 'customerTicketsCount', $customerTicketsCount ?? 0 )
            ->with( 'need_act', $need_act );

    }

    public function saveServices ( Request $request, $ticket_management_id )
    {

        $ticketManagement = TicketManagement
            ::find( $ticket_management_id );
        if ( ! $ticketManagement )
        {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors( [ 'Заявка не найдена' ] );
        }

        $rules = [
            'services.*.name'				        => 'required|string',
            'services.*.quantity'				    => 'required|numeric|min:1',
            'services.*.unit'				        => 'required|string',
            'services.*.amount'				        => 'required|numeric|min:0',
        ];

        $this->validate( $request, $rules );

        $res = $ticketManagement->saveServices( $request->get( 'services', [] ) );
        if ( $res instanceof MessageBag )
        {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors( $res );
        }

        return redirect()
            ->back()
            ->with( 'success', 'Выполненные работы успешно сохранены' );

    }

    public function select ( Request $request, $id )
    {

        $ticket = Ticket::find( $id );
        if ( ! $ticket )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Заявка не найдена' );
        }

        if ( $ticket->type_id && $ticket->building_id )
        {

            $managements = Management
                ::mine()
                ->select(
                    Management::$_table . '.*'
                )
                ->leftJoin( Management::$_table . ' AS parent', 'parent.id', '=', Management::$_table . '.parent_id' )
                ->whereHas( 'types', function ( $types ) use ( $ticket )
                {
                    return $types
                        ->where( Type::$_table . '.id', '=', $ticket->type_id );
                })
                ->whereHas( 'buildings', function ( $buildings ) use ( $ticket )
                {
                    return $buildings
                        ->where( Building::$_table . '.id', '=', $ticket->building_id );
                })
                ->orderBy( 'parent.name' )
                ->orderBy( Management::$_table . '.name' )
                ->get();

            $worksCount = Work
                ::current()
                ->whereHas( 'buildings', function ( $buildings ) use ( $ticket )
                {
                    return $buildings
                        ->where( Building::$_table . '.id', '=', $ticket->building_id );
                })
                ->where( 'category_id', '=', $ticket->type->parent_id ?: $ticket->type->id )
                ->count();

            $neighborsTicketsCount = $ticket
                ->neighborsTickets()
                ->whereHas( 'managements', function ( $managements )
                {
                    return $managements
                        ->mine();
                })
                ->where( 'phone', '!=', $ticket->phone )
                ->where( 'flat', '!=', $ticket->flat )
                ->count();

            $addressTicketsCount = $ticket
                ->whereHas( 'managements', function ( $managements )
                {
                    return $managements
                        ->mine();
                })
                ->where( 'building_id', '=', $ticket->building_id )
                ->where( 'flat', '=', $ticket->flat )
                ->count();

            if ( $ticket->phone )
            {
                $customerTicketsCount = $ticket
                    ->customerTickets()
                    ->whereHas( 'managements', function ( $managements )
                    {
                        return $managements
                            ->mine();
                    })
                    ->count();
            }

        }
        else if ( $ticket->phone )
        {
            $customerTickets = $ticket
                ->customerTickets()
                ->whereHas( 'managements', function ( $managements )
                {
                    return $managements
                        ->mine();
                })
                ->paginate( config( 'pagination.per_page' ) );
            $customerTicketsCount = $customerTickets->count();
        }

        return view( 'tickets.parts.select' )
            ->with( 'ticket', $ticket )
            ->with( 'managements', $managements ?? collect() )
            ->with( 'worksCount', $worksCount ?? 0 )
            ->with( 'neighborsTicketsCount', $neighborsTicketsCount ?? 0 )
            ->with( 'addressTicketsCount', $addressTicketsCount ?? 0 )
            ->with( 'customerTicketsCount', $customerTicketsCount ?? 0 )
            ->with( 'customerTickets', $customerTickets ?? collect() );

    }

    public function history ( Request $request, $ticket_id, $ticket_management_id = null )
    {

        $ticket = Ticket
            ::mine()
            ->find( $ticket_id );
        if ( ! $ticket )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Заявка не найдена' );
        }

        if ( $ticket_management_id )
        {
            $ticketManagement = $ticket->managements()->mine()->find( $ticket_management_id );
            if ( ! $ticketManagement )
            {
                return view( 'parts.error' )
                    ->with( 'error', 'Заявка не найдена' );
            }
            $ticketManagementLogs = $ticketManagement
                ->logs()
                ->with(
                    'author'
                )
                ->get();
        }

        $ticketLogs = $ticket
            ->logs()
            ->with(
                'author'
            )
            ->get();

        if ( $ticket_management_id )
        {
            $logs = $ticketLogs->merge( $ticketManagementLogs )->sortBy( 'created_at' );
        }
        else
        {
            $logs = $ticketLogs;
        }

        $statuses = $ticket
            ->statusesHistory()
            ->orderBy( 'id' )
            ->with( 'author' )
            ->get();

        return view( 'tickets.tabs.history' )
            ->with( 'ticket', $ticket )
            ->with( 'statuses', $statuses )
            ->with( 'logs', $logs );

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit ( Request $request, $id )
    {

        $ticket = Ticket::find( $id );
		$param = $request->get( 'param' );
		$id = $request->get( 'id' );

		switch ( $param )
		{

            case 'rate':

                if ( \Auth::user()->can( 'tickets.rate' ) )
                {
                    $ticketManagement = $ticket->managements()->mine()->find( $id );
                    if ( $ticketManagement )
                    {
                        return view( 'tickets.parts.rate_form' )
                            ->with( 'ticketManagement', $ticketManagement );
                    }
                    else
                    {
                        return view( 'parts.error' )
                            ->with( 'error', 'Заявка не найдена' );
                    }
                }
                else
                {
                    return view( 'parts.error' )
                        ->with( 'error', 'Ошибка доступа' );
                }

                break;
			
			case 'type':

			    if ( \Auth::user()->can( 'tickets.edit' ) )
                {
                    $res = Type
                        ::mine()
                        ->where( function ( $q ) use ( $ticket )
                        {
                            return $q
                                ->whereNotNull( 'parent_id' )
                                ->orWhere( 'id', '=', $ticket->type_id );
                        })
                        ->orderBy( 'name' )
                        ->get();
                    $types = [];
                    foreach ( $res as $r )
                    {
                        $types[ $r->parent->name ?? 'Текущий' ][ $r->id ] = $r->name;
                    }
                    return view( 'tickets.edit.type' )
                        ->with( 'ticket', $ticket )
                        ->with( 'types', $types )
                        ->with( 'param', $param );
                }
                else
                {
                    return view( 'parts.error' )
                        ->with( 'error', 'Ошибка доступа' );
                }
			
				break;
				
			case 'building':

                if ( \Auth::user()->can( 'tickets.edit' ) )
                {
                    return view( 'tickets.edit.building' )
                        ->with( 'ticket', $ticket )
                        ->with( 'param', $param );
                }
                else
                {
                    return view( 'parts.error' )
                        ->with( 'error', 'Ошибка доступа' );
                }
			
				break;

            case 'actual_building':

                if ( \Auth::user()->can( 'tickets.edit' ) )
                {
                    return view( 'tickets.edit.actual_building' )
                        ->with( 'ticket', $ticket )
                        ->with( 'param', $param );
                }
                else
                {
                    return view( 'parts.error' )
                        ->with( 'error', 'Ошибка доступа' );
                }

                break;
				
			case 'mark':

                if ( \Auth::user()->can( 'tickets.edit' ) )
                {
                    return view( 'tickets.edit.mark' )
                        ->with( 'ticket', $ticket )
                        ->with( 'param', $param );
                }
                else
                {
                    return view( 'parts.error' )
                        ->with( 'error', 'Ошибка доступа' );
                }
			
				break;
				
			case 'text':

                if ( \Auth::user()->can( 'tickets.edit' ) )
                {
                    return view( 'tickets.edit.text' )
                        ->with( 'ticket', $ticket )
                        ->with( 'param', $param );
                }
                else
                {
                    return view( 'parts.error' )
                        ->with( 'error', 'Ошибка доступа' );
                }
			
				break;
				
			case 'name':

                if ( \Auth::user()->can( 'tickets.edit' ) )
                {
                    return view( 'tickets.edit.name' )
                        ->with( 'ticket', $ticket )
                        ->with( 'param', $param );
                }
                else
                {
                    return view( 'parts.error' )
                        ->with( 'error', 'Ошибка доступа' );
                }
			
				break;
				
			case 'phone':

                if ( \Auth::user()->can( 'tickets.edit' ) )
                {
                    return view( 'tickets.edit.phone' )
                        ->with( 'ticket', $ticket )
                        ->with( 'param', $param );
                }
                else
                {
                    return view( 'parts.error' )
                        ->with( 'error', 'Ошибка доступа' );
                }

			
				break;

            case 'schedule':

                if ( \Auth::user()->can( 'tickets.executor' ) )
                {
                    return view( 'tickets.edit.schedule' )
                        ->with( 'ticket', $ticket )
                        ->with( 'param', $param );
                }
                else
                {
                    return view( 'parts.error' )
                        ->with( 'error', 'Ошибка доступа' );
                }

                break;
				
		}

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update ( Request $request, $id )
    {
		
        $ticket = Ticket::find( $id );
		if ( ! $ticket )
		{
			return redirect()
                ->route( 'tickets.index' )
				->withErrors( [ 'Заявка не найдена' ] );
		}

		$attributes = $request->all();

		if ( ! empty( $attributes[ 'scheduled_begin_date' ] ) && ! empty( $attributes[ 'scheduled_begin_time' ] ) )
        {
            $attributes[ 'scheduled_begin' ] = Carbon::parse( $attributes[ 'scheduled_begin_date' ] . ' ' . $attributes[ 'scheduled_begin_time' ] )->toDateTimeString();
        }
        if ( ! empty( $attributes[ 'scheduled_end_date' ] ) && ! empty( $attributes[ 'scheduled_end_time' ] ) )
        {
            $attributes[ 'scheduled_end' ] = Carbon::parse( $attributes[ 'scheduled_end_date' ] . ' ' . $attributes[ 'scheduled_end_time' ] )->toDateTimeString();
        }
		
		$res = $ticket->edit( $attributes );

		if ( $res instanceof MessageBag )
        {
            if ( $request->ajax() )
            {
                $error = $res->first();
                return compact( 'error' );
            }
            else
            {
                return redirect()
                    ->back()
                    ->withErrors( $res );
            }
        }

        $success = 'Заявка успешно отредактирована';

        if ( $request->ajax() )
        {
            return compact( 'success' );
        }
        else
        {
            return redirect()
                ->back()
                ->with( 'success', $success );
        }
		
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function changeStatus ( Request $request, $ticket_id, $ticket_management_id = null )
    {

        try
        {

            $ticket = Ticket
                ::mine()
                ->find( $ticket_id );

            if ( ! $ticket )
            {
                return redirect()
                    ->route( 'tickets.index' )
                    ->withErrors( [ 'Заявка не найдена' ] );
            }

            \DB::beginTransaction();

            if ( ! empty( $request->get( 'postponed_to' ) ) )
            {
                $ticket->postponed_to = Carbon::parse( $request->get( 'postponed_to' ) )->toDateString();
                if ( ! empty( $request->get( 'postponed_comment' ) ) )
                {
                    $ticket->postponed_comment = $request->get( 'postponed_comment' );
                }
                $ticket->save();
            }

            if ( $ticket_management_id )
            {
                $ticketManagement = $ticket
                    ->managements()
                    ->mine()
                    ->find( $ticket_management_id );
                if ( ! $ticketManagement )
                {
                    return redirect()
                        ->route( 'tickets.index' )
                        ->withErrors( [ 'Заявка не найдена' ] );
                }
                $res = $ticketManagement->changeStatus( $request->get( 'status_code' ) );
            }
            else
            {
                $res = $ticket->changeStatus( $request->get( 'status_code' ) );
            }

            if ( ! empty( $request->get( 'comment' ) ) )
            {
                $res = $ticket->addComment( $request->get( 'comment' ) );
                if ( $res instanceof MessageBag )
                {
                    return redirect()->back()
                        ->withErrors( $res );
                }
            }

            if ( $res instanceof MessageBag )
            {
                return redirect()->back()
                    ->withErrors( $res );
            }

            foreach ( $ticket->managements as $ticketManagement )
            {
                $mosreg = null;
                if ( $ticketManagement->mosreg_id && $ticketManagement->management && $ticketManagement->management->hasMosreg( $mosreg ) )
                {
                    if ( ! empty( $request->get( 'reject_reason_id' ) ) )
                    {
                        $mosreg->answer( $ticketManagement->mosreg_id, $request->get( 'reject_reason_id' ) );
                        $ticketManagement->changeMosregStatus( 'ANSWERED', false );
                    }
                    if ( ! empty( $request->get( 'postponed_to' ) ) )
                    {
                        $comment = 'Отложено до ' . $ticket->postponed_to->format( 'd.m.Y' ) . PHP_EOL . $ticket->postponed_comment;
                        $mosreg->answer( $ticketManagement->mosreg_id, 5076, $comment );
                        $ticketManagement->changeMosregStatus( 'ANSWERED', false );
                    }
                }
            }

            \DB::commit();

            \Cache::tags( 'tickets_counts' )->flush();

            return redirect()->back()->with( 'success', 'Статус изменен' );

        }
        catch ( \Exception $e )
        {
            return redirect()->back()->withErrors( [ 'Внутренняя ошибка системы!' ] );
        }

    }

    public function getPostponed ( Request $request )
    {
        $this->validate( $request, [
            'ticket_id'      => 'required|integer',
        ]);
        $ticket = Ticket
            ::mine()
            ->find( $request->get( 'ticket_id' ) );
        if ( ! $ticket )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Заявка не найдена' );
        }
        return view( 'tickets.edit.postponed' )
            ->with( 'ticket', $ticket );
    }

    public function postPostponed ( Request $request, $ticket_id )
    {
        $this->validate( $request, [
            'postponed_to'              => 'required|date',
            'postponed_comment'         => 'nullable',
        ]);
        $ticket = Ticket
            ::mine()
            ->find( $ticket_id );
        if ( ! $ticket )
        {
            return redirect()->back()
                ->withErrors( [ 'Заявка не найдена' ] );
        }
        $ticket->postponed_to = Carbon::parse( $request->get( 'postponed_to' ) )->toDateString();
        if ( ! empty( $request->get( 'postponed_comment' ) ) )
        {
            $ticket->postponed_comment = $request->get( 'postponed_comment' );
        }
        $ticket->save();
        $res = $ticket->changeStatus( 'waiting', true );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }
        return redirect()->back()->with( 'success', 'Статус изменен' );
    }

    public function postpone ( Request $request, $id )
    {
        $ticket = Ticket::find( $id );
        return view( 'modals.postpone' )
            ->with( 'ticket', $ticket )
            ->with( 'model_id', $request->get( 'model_id' ) )
            ->with( 'model_name', $request->get( 'model_name' ) )
            ->with( 'status_code', $request->get( 'status_code' ) );
    }

    public function action ( Request $request )
    {

        \DB::beginTransaction();

        switch ( $request->get( 'action' ) )
        {

            case 'group':

                if ( count( $request->get( 'tickets', [] ) ) < 2 )
                {
                    return redirect()->back()->withErrors( [ 'Для группировки необходимо выбрать 2 или более заявок' ] );
                }

                $tickets = Ticket
                    ::whereIn( 'id', $request->get( 'tickets' ) )
                    //->orderBy( 'id', 'desc' )
                    ->get();

                if ( $tickets->count() != count( $request->get( 'tickets' ) ) )
                {
                    return redirect()->back()->withErrors( [ 'Количество выбранных заявок не совпадает с количество найденных!' ] );
                }

                $uuid = Uuid::uuid4()->toString();
                $parent = null;
                foreach ( $tickets as $ticket )
                {
                    $ticket->group_uuid = $uuid;
                    if ( is_null( $parent ) )
                    {
                        $ticket->parent_id = null;
                        $parent = $ticket;
                    }
                    else
                    {
                        $ticket->parent_id = $parent->id;
                    }
                    $ticket->save();
                }

                break;

            case 'ungroup':

                if ( count( $request->get( 'tickets', [] ) ) < 1 )
                {
                    return redirect()->back()->withErrors( [ 'Выберите хотя бы одну заявку' ] );
                }

                $tickets = Ticket
                    ::whereIn( 'id', $request->get( 'tickets' ) )
                    //->orderBy( 'id', 'desc' )
                    ->get();

                foreach ( $tickets as $ticket )
                {
                    $group = $ticket->group()
                        ->whereNotIn( 'id', $tickets->pluck( 'id' )->toArray() )
                        ->get();
                    if ( $group->count() == 1 )
                    {
                        $group[0]->group_uuid = null;
                        $group[0]->parent_id = null;
                        $group[0]->save();
                    }
                    else if ( ! $ticket->parent_id )
                    {
                        $parent = null;
                        foreach ( $group as $row )
                        {
                            if ( is_null( $parent ) )
                            {
                                $row->parent_id = null;
                                $parent = $row;
                            }
                            else
                            {
                                $row->parent_id = $parent->id;
                            }
                            $row->save();
                        }
                    }
                    $ticket->group_uuid = null;
                    $ticket->parent_id = null;
                    $ticket->save();
                }

                break;

            case 'delete':

                if ( count( $request->get( 'tickets', [] ) ) < 1 )
                {
                    return redirect()->back()->withErrors( [ 'Выберите хотя бы одну заявку' ] );
                }

                $tickets = Ticket
                    ::whereIn( 'id', $request->get( 'tickets' ) )
                    //->orderBy( 'id', 'desc' )
                    ->get();

                foreach ( $tickets as $ticket )
                {
                    $ticket->delete();
                }

                break;

            default:
                return redirect()->back()->withErrors( [ 'Некорректное действие' ] );
                break;

        }

        \DB::commit();

        return redirect()->back()->with( 'success', 'Готово' );

    }

    public function act ( Request $request, $ticket_id, $ticket_management_id )
    {

        $ticket = Ticket
            ::whereHas( 'managements', function ( $managements )
            {
                return $managements
                    ->mine();
            })
            ->find( $ticket_id );
        if ( ! $ticket )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }

        $ticketManagement = $ticket
            ->managements()
            ->find( $ticket_management_id );
        if ( ! $ticketManagement )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }

        $ticketManagement->addLog( 'Распечатал акт №' . $ticketManagement->getTicketNumber() );

        $act = null;

        if ( $ticketManagement->act )
        {
            $act = $ticketManagement->act;
        }
        else if ( $ticketManagement->management->acts->count() )
        {
            $act = $ticketManagement->management->acts->first();
        }
        else if ( $ticketManagement->management->parent && $ticketManagement->management->parent->acts->count() )
        {
            $act = $ticketManagement->management->parent->acts->first();
        }

        if ( $act )
        {

            $content = $act->getPreparedContent( $ticketManagement );

            return view( 'tickets.management_act' )
                ->with( 'content', $content );

        }
        else
        {

            $services = $ticketManagement->services;

            $lines = 10 - $services->count();
            if ( $lines < 0 ) $lines = 0;

            $total = $services->sum( function ( $service ){ return $service[ 'amount' ] * $service[ 'quantity' ]; } );

            return view( 'tickets.act' )
                ->with( 'ticketManagement', $ticketManagement )
                ->with( 'services', $services )
                ->with( 'total', $total )
                ->with( 'lines', $lines );

        }

    }

    public function waybill ( Request $request )
    {

        Title::set( 'Наряд-заказ' );

        if ( ! \Auth::user()->can( 'tickets.waybill' ) )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Доступ запрещен' ] );
        }

        $ids = explode( ',', $request->get( 'ids', '' ) );
        if ( ! count( $ids ) )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявки не выбраны' ] );
        }

        $ticketManagements = TicketManagement
            ::mine()
            ->whereIn( 'id', $ids )
            ->get();
        if ( ! $ticketManagements->count() )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявки не найдены' ] );
        }

        return view( 'tickets.waybill' )
            ->with( 'ticketManagements', $ticketManagements );

    }

    public function getExecutor ( Request $request, $ticket_management_id = null )
    {

        $ticketManagement = TicketManagement::find( $request->get( 'ticket_management_id', $ticket_management_id ) );
        if ( ! $ticketManagement )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Заявка не найдена' );
        }

        $management = $ticketManagement->management;
        $executors = [ null => 'Выбрать из списка' ] + $management->executors()->pluck( 'name', 'id' )->toArray();
		
        return view( 'tickets.edit.executor' )
            ->with( 'ticketManagement', $ticketManagement )
            ->with( 'management', $management )
            ->with( 'executors', $executors );
			
    }

    public function postExecutor ( Request $request, $id )
    {

        $this->validate( $request, [
            'executor_id'               => 'required_without:executor_name|nullable|integer',
            'executor_name'             => 'required_without:executor_id|nullable',
            'scheduled_begin_date'      => 'required|date|date_format:Y-m-d',
            'scheduled_begin_time'      => 'required|date_format:H:i',
            'scheduled_end_date'        => 'required|date|date_format:Y-m-d|after_or_equal:scheduled_begin_date',
            'scheduled_end_time'        => 'required|date_format:H:i',
        ]);

        $scheduled_begin = Carbon::parse( $request->get( 'scheduled_begin_date' ) . ' ' . $request->get( 'scheduled_begin_time' ) );
        $scheduled_end = Carbon::parse( $request->get( 'scheduled_end_date' ) . ' ' . $request->get( 'scheduled_end_time' ) );

        if ( $scheduled_begin->timestamp < 0 || $scheduled_end->timestamp < 0  )
        {
            return redirect()->back()->withErrors( [ 'Некорректная дата' ] );
        }

        $ticketManagement = TicketManagement::find( $id );
        if ( ! $ticketManagement )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }

        $ticket = $ticketManagement->ticket;

        \DB::beginTransaction();
        if ( $request->get( 'executor_id' ) )
        {
            $executor = Executor::find( $request->get( 'executor_id' ) );
            if ( ! $executor )
            {
                return redirect()
                    ->back()
                    ->withErrors( [ 'Исполнитель не найден' ] );
            }
        }
        else if ( $request->get( 'executor_name' ) )
        {
            $executor = $ticketManagement
                ->management
                ->executors()
                ->withTrashed()
                ->whereRaw( 'REPLACE( name, \' \', \'\' ) like ?', [ '%' . str_replace( ' ', '', $request->get( 'executor_name' ) ) . '%' ] )
                ->first();
            if ( ! $executor )
            {
                $executor = Executor::create([
                    'management_id'     => $ticketManagement->management->id,
                    'name'              => $request->get( 'executor_name' )
                ]);
                if ( $executor instanceof MessageBag )
                {
                    if ( $request->ajax() )
                    {
                        $error = $executor->first();
                        return compact( 'error' );
                    }
                    else
                    {
                        return redirect()
                            ->back()
                            ->withErrors( $executor );
                    }
                }
            }
            else
            {
                $executor->deleted_at = null;
            }
            $executor->save();
        }
        $attributes = [
            'executor_id'           => $executor->id,
            'scheduled_begin'       => $scheduled_begin->toDateTimeString(),
            'scheduled_end'         => $scheduled_end->toDateTimeString(),
        ];
        $ticketManagement->fill( $attributes );
        if ( $ticketManagement->isDirty() )
        {
            $ticketManagement->save();
            $res = $ticketManagement->addLog( 'Назначен исполнитель "' . $executor->name . '" на время с "' . $ticketManagement->scheduled_begin->format( 'd.m.Y H:i' ) . '" до "' . $ticketManagement->scheduled_end->format( 'd.m.Y H:i' ) . '"' );
            if ( $res instanceof MessageBag )
            {
                if ( $request->ajax() )
                {
                    $error = $res->first();
                    return compact( 'error' );
                }
                else
                {
                    return redirect()
                        ->back()
                        ->withErrors( $res );
                }
            }
            $res = $ticketManagement->changeStatus( 'assigned', true );
            if ( $res instanceof MessageBag )
            {
                if ( $request->ajax() )
                {
                    $error = $res->first();
                    return compact( 'error' );
                }
                else
                {
                    return redirect()
                        ->back()
                        ->withErrors( $res );
                }
            }
            $this->dispatch( new SendStream( 'update', $ticketManagement ) );
        }
        \DB::commit();

        $success = 'Исполнитель успешно назначен';

        \Cache::tags( 'tickets.scheduled.now' )->flush();

        if ( $request->ajax() )
        {
            return compact( 'success' );
        }
        else
        {
            return redirect()
                ->back()
                ->with( 'success', $success );
        }

    }

    public function checkExecutor ( Request $request )
    {

        $this->validate( $request, [
            'executor_id'               => 'required|integer',
            'scheduled_begin_date'      => 'required|date_format:Y-m-d',
            'scheduled_begin_time'      => 'required|date_format:H:i',
            'scheduled_end_date'        => 'required|date_format:Y-m-d',
            'scheduled_end_time'        => 'required|date_format:H:i',
        ]);

        $scheduled_begin = Carbon::parse( $request->get( 'scheduled_begin_date' ) . ' ' . $request->get( 'scheduled_begin_time' ) );
        $scheduled_end = Carbon::parse( $request->get( 'scheduled_end_date' ) . ' ' . $request->get( 'scheduled_end_time' ) );

        if ( $scheduled_begin->timestamp > $scheduled_end->timestamp )
        {
            return new MessageBag( [ 'Дата начала не может быть поздне даты окончания' ] );
        }

        $scheduled_begin = $scheduled_begin->toDateTimeString();
        $scheduled_end = $scheduled_end->toDateTimeString();

        $ticketManagement = TicketManagement
            ::whereBetween( 'scheduled_begin', [ $scheduled_begin, $scheduled_end ] )
            ->whereBetween( 'scheduled_end', [ $scheduled_begin, $scheduled_end ] )
            ->where( 'executor_id', '=', $request->get( 'executor_id' ) )
            ->first();

        if ( $ticketManagement )
        {
            return [
                'finded' => [
                    'number'                => $ticketManagement->getTicketNumber(),
                    'scheduled_begin'       => $ticketManagement->scheduled_begin->format( 'd.m.Y H:i' ),
                    'scheduled_end'         => $ticketManagement->scheduled_end->format( 'd.m.Y H:i' )
                ]
            ];
        }

        return '0';

    }

    public function getManagements ( Request $request, $id )
    {

        $ticketManagement = TicketManagement::find( $id );
        if ( ! $ticketManagement )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Заявка не найдена' );
        }

        $ticket = $ticketManagement->ticket;

        $managements = Management
            ::mine( Management::IGNORE_MANAGEMENT )
            ->select(
                Management::$_table . '.*'
            )
            ->leftJoin( Management::$_table . ' AS parent', 'parent.id', '=', Management::$_table . '.parent_id' )
            ->where( Management::$_table . '.id', '=', $ticketManagement->management->id )
            ->orWhere( function ( $q ) use ( $ticketManagement, $ticket )
            {
                $q
                    ->whereHas( 'types', function ( $types ) use ( $ticket )
                    {
                        return $types
                            ->where( Type::$_table . '.id', '=', $ticket->type_id );
                    })
                    ->whereHas( 'buildings', function ( $buildings ) use ( $ticket )
                    {
                        return $buildings
                            ->where( Building::$_table . '.id', '=', $ticket->building_id );
                    });
                if ( $ticketManagement->management->parent_id )
                {
                    $q
                        ->where( function ( $q2 ) use ( $ticketManagement )
                        {
                            return $q2
                                ->whereNull( Management::$_table . '.parent_id' )
                                ->orWhere( Management::$_table . '.parent_id', '=', $ticketManagement->management->id )
                                ->orWhere( Management::$_table . '.parent_id', '=', $ticketManagement->management->parent_id );
                        });
                }
                return $q;
            })
            ->orderBy( 'parent.name' )
            ->orderBy( Management::$_table . '.name' )
            ->get();

        return view( 'tickets.edit.managements' )
            ->with( 'ticketManagement', $ticketManagement )
            ->with( 'ticket', $ticket )
            ->with( 'managements', $managements );

    }

    public function postManagements ( Request $request, $id )
    {

        $this->validate( $request, [
            'management_id'               => 'required|integer',
        ]);

        $ticketManagement = TicketManagement::find( $id );
        if ( ! $ticketManagement )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Заявка не найдена' ] );
        }

        $ticket = $ticketManagement->ticket;

        \DB::beginTransaction();

        $managements = Management
            ::mine( Management::IGNORE_MANAGEMENT )
            ->where( Management::$_table . '.id', '=', $ticketManagement->management->id )
            ->orWhere( function ( $q ) use ( $ticketManagement, $ticket )
            {
                $q
                    ->whereHas( 'types', function ( $types ) use ( $ticket )
                    {
                        return $types
                            ->where( Type::$_table . '.id', '=', $ticket->type_id );
                    })
                    ->whereHas( 'buildings', function ( $buildings ) use ( $ticket )
                    {
                        return $buildings
                            ->where( Building::$_table . '.id', '=', $ticket->building_id );
                    });
                if ( $ticketManagement->management->parent_id )
                {
                    $q
                        ->where( function ( $q2 ) use ( $ticketManagement )
                        {
                            return $q2
                                ->whereNull( Management::$_table . '.parent_id' )
                                ->orWhere( Management::$_table . '.parent_id', '=', $ticketManagement->management->id )
                                ->orWhere( Management::$_table . '.parent_id', '=', $ticketManagement->management->parent_id );
                        });
                }
                return $q;
            })
            ->get();

        $management = $managements->where( 'id', $request->get( 'management_id' ) )->first();

        if ( ! $management )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Невозможно назначить УО' ] );
        }

        $ticketManagement->management_id = $management->id;

        if ( $ticketManagement->isDirty() )
        {
            $ticketManagement->executor_id = null;
            $ticketManagement->scheduled_begin = null;
            $ticketManagement->scheduled_end = null;
            $ticketManagement->save();
            $management_name = $management->name;
            if ( $management->parent )
            {
                $management_name = $management->parent->name . ' ' . $management_name;
            }
            $res = $ticketManagement->addLog( 'Назначено УО "' . $management_name . '"' );
            if ( $res instanceof MessageBag )
            {
                return redirect()->back()
                    ->withErrors( $res );
            }
            $this->dispatch( new SendStream( 'update', $ticketManagement ) );
        }

        \DB::commit();

        return redirect()->back()->with( 'success', 'УО успешно назначено' );

    }

    public function getRateForm ( Request $request, $id )
    {
        $ticketManagement = TicketManagement::find( $id );
        if ( ! $ticketManagement )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Заявка не найдена' );
        }
        if ( $ticketManagement->rate )
        {
            return view( 'parts.error' )
                ->with( 'error', 'По данной заявке уже имеется оценка' );
        }
        return view( 'tickets.parts.rate_form' )
            ->with( 'ticketManagement', $ticketManagement )
            ->with( 'closed_with_confirm', 1 );
    }

    public function postRateForm ( Request $request, $id )
    {
        $ticketManagement = TicketManagement::find( $id );
        if ( ! $ticketManagement )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }
        \DB::beginTransaction();
        $ticketManagement->rate = $request->get( 'rate' );
        $ticketManagement->rate_comment = $request->get( 'comment', null );
        $ticketManagement->save();
        if ( $ticketManagement->rate_comment )
        {
            $res = $ticketManagement->addLog( 'Поставлена оценка "' . $ticketManagement->rate . '" с комментарием "' . $ticketManagement->rate_comment . '"' );
        }
        else
        {
            $res = $ticketManagement->addLog( 'Поставлена оценка "' . $ticketManagement->rate . '"' );
        }
        if ( $res instanceof MessageBag )
        {
			return redirect()
                ->route( 'tickets.index' )
                ->withErrors( $res );
        }
		if ( $request->get( 'closed_with_confirm', 0 ) == 1 && $ticketManagement->status_code != 'closed_with_confirm' )
        {
            $res = $ticketManagement->changeStatus( 'closed_with_confirm', true );
            if ( $res instanceof MessageBag )
            {
                return redirect()->back()->withErrors( $res );
            }
        }
		\DB::commit();
        return redirect()->back()->with( 'success', 'Ваша оценка учтена' );
    }

    public function postSave ( Request $request, $id )
    {

        $ticket = Ticket::find( $id );
        if ( ! $ticket ) return;

        switch ( $request->get( 'field' ) )
        {
            case 'tags':
                $tags = explode( ',', $request->get( 'value' ) );
                foreach ( $tags as $tag )
                {
                    $tag = trim( $tag );
                    if ( empty( $tag ) || $ticket->tags()->where( 'text', '=', $tag )->count() ) continue;
                    $ticket->addTag( $tag );
                }
                break;
            case 'type_id':
                $res = $ticket->edit([
                    $request->get( 'field' ) => $request->get( 'value', 0 ) ?: null
                ]);
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }
                break;
            default:
                $res = $ticket->edit([
                    $request->get( 'field' ) => $request->get( 'value' )
                ]);
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }
                break;
        }

        return [
            'can_create_user' => (int) $ticket->canCreateUser( true ) ? 1 : 0
        ];

    }

    public function addTag ( Request $request, $id )
    {
        $ticket = Ticket::find( $id );
        if ( ! $ticket ) return;
        $tag = trim( $request->get( 'tag', '' ) );
        if ( empty( $tag ) || $ticket->tags()->where( 'text', '=', $tag )->count() ) return;
        $ticket->addTag( $tag );
    }

    public function delTag ( Request $request, $id )
    {
        $ticket = Ticket::find( $id );
        if ( ! $ticket ) return;
        $tag = $ticket->tags()->where( 'text', '=', trim( $request->get( 'tag', '' ) ) )->first();
        if ( ! $tag ) return;
        $tag->delete();
    }

    public function cancel ( Request $request, $id )
    {
        $ticket = Ticket::find( $id );
        if ( ! $ticket )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }
        if ( $ticket->status_code != 'draft' )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Невозможно отменить добавленную заявку' ] );
        }
        $ticket->fill([
            'provider_id'               => Provider::getCurrent()->id ?? null,
            'type_id'                   => null,
            'building_id'               => null,
            'place_id'                  => null,
            'flat'                      => null,
            'actual_building_id'        => null,
            'actual_flat'               => null,
            'phone'                     => null,
            'phone2'                    => null,
            'customer_id'               => null,
            'text'                      => null,
            'firstname'                 => null,
            'lastname'                  => null,
            'middlename'                => null,
        ]);
        $ticket->emergency = 0;
        $ticket->urgently = 0;
        $ticket->dobrodel = 0;
        $ticket->save();
        return redirect()
            ->back()
            ->with( 'success', 'Данные заявки очищены' );
    }

    public function search ( Request $request )
    {

        if ( ! \Auth::user()->can( 'catalog.customers.tickets' ) )
        {
            return null;
        }

        $phone = str_replace( '+7', '', $request->get( 'phone' ) );
        $phone = mb_substr( preg_replace( '/[^0-9]/', '', $phone ), -10 );

        $tickets = Ticket
            ::whereHas( 'managements', function ( $managements )
            {
                return $managements
                    ->mine();
            })
            ->where( 'phone', '=', $phone )
            ->where( 'status_code', '!=', 'draft' )
            ->orderBy( 'id', 'desc' )
            ->take( config( 'pagination.per_page' ) )
            ->get();

        if ( $tickets->count() )
        {
            return view( 'tickets.select' )
                ->with( 'tickets', $tickets );
        }

    }

    public function filter ( Request $request )
    {

        if ( ! \Auth::user()->can( 'tickets.search' ) )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Доступ запрещен' ] );
        }

        $data = $request->all();

        unset( $data[ '_token' ] );

        foreach ( $data as $key => $val )
        {
            if ( empty( $val ) )
            {
                unset( $data[ $key ] );
            }
        }

        if ( isset( $data[ 'statuses' ] ) )
        {
            if ( ! count( $data[ 'statuses' ] ) || count( $data[ 'statuses' ] ) == count( \Auth::user()->getAvailableStatuses( 'show' ) ) )
            {
                unset( $data[ 'statuses' ] );
            }
            else
            {
                $data[ 'statuses' ] = implode( ',', $data[ 'statuses' ] );
            }
        }

        if ( isset( $data[ 'managements' ] ) )
        {
            $data[ 'managements' ] = implode( ',', $data[ 'managements' ] );
        }

        if ( isset( $data[ 'operators' ] ) )
        {
            $data[ 'operators' ] = implode( ',', $data[ 'operators' ] );
        }

        if ( isset( $data[ 'types' ] ) )
        {
            if ( ! count( $data[ 'types' ] ) || count( $data[ 'types' ] ) == Type::count() )
            {
                unset( $data[ 'types' ] );
            }
            else
            {
                $data[ 'types' ] = implode( ',', $data[ 'types' ] );
            }
        }

        if ( isset( $data[ 'phone' ] ) )
        {
            $data[ 'phone' ] = str_replace( '+7', '', $data[ 'phone' ] );
            $data[ 'phone' ] = preg_replace( '/[^0-9_]/', '', $data[ 'phone' ] );
        }

        if ( $request->ajax() )
        {
            return $data;
        }
        else
        {
            return redirect()->route( 'tickets.index', $data );
        }

    }

    public function calendar ( Request $request, $date )
    {
        Title::add( 'Календарь' );
        list ( $month, $year ) = explode( '.', $date );
        $beginDate = Carbon::create( $year, $month, 1 )->setTime( 0, 0, 0 )->toDateTimeString();
        $endDate = Carbon::create( $year, $month, 1 )->endOfMonth()->setTime( 23, 59, 59 )->toDateTimeString();
        $res = Management
            ::mine()
            ->whereHas( 'parent' )
            ->with( 'parent' )
            ->get()
            ->sortBy( 'name' );
        $availableManagements = [];
        foreach ( $res as $r )
        {
            $availableManagements[ $r->parent->name ][ $r->id ] = $r->name;
        }
        return view( 'tickets.calendar' )
            ->with( 'date', $date )
            ->with( 'beginDate', $beginDate )
            ->with( 'endDate', $endDate )
            ->with( 'availableManagements', $availableManagements );
    }

    public function calendarData ( Request $request )
    {

        list ( $month, $year ) = explode( '.', $request->get( 'date' ) );
        $beginDate = Carbon::create( $year, $month, 1 );
        $endDate = Carbon::create( $year, $month, 1 )->endOfMonth();

        $ticketManagements = TicketManagement
            ::mine()
            ->whereHas( 'ticket', function ( $ticket ) use ( $request )
            {

                $ticket->notCompleted();

                if ( $request->get( 'building_id' ) )
                {
                    $ticket
                        ->where( Ticket::$_table . '.building_id', $request->get( 'building_id' ) );
                }

                if ( ! empty( $request->get( 'segments' ) ) )
                {
                    $segments = Segment::whereIn( 'id', $request->get( 'segments' ) )->get();
                    if ( $segments->count() )
                    {
                        $segmentIds = [];
                        foreach ( $segments as $segment )
                        {
                            $segmentChilds = new SegmentChilds( $segment );
                            $segmentIds += $segmentChilds->ids;
                        }
                        $ticket
                            ->whereHas( 'building', function ( $building ) use ( $segmentIds )
                            {
                                return $building
                                    ->whereIn( Building::$_table . '.segment_id', $segmentIds );
                            });
                    }
                }

            })
            ->whereNotNull( TicketManagement::$_table . '.scheduled_end' )
            ->where( \DB::raw( 'YEAR( scheduled_begin )' ), '=', $year )
            ->where( \DB::raw( 'MONTH( scheduled_begin )' ), '=', $month );

        if ( count( $request->get( 'managements', [] ) ) )
        {
            $ticketManagements
                ->whereIn( TicketManagement::$_table . '.management_id', $request->get( 'managements', [] ) );
        }

        $ticketManagements = $ticketManagements->get();

        $data = [
            'events' => [],
            'beginDate' => $beginDate->format( 'Y-m-d' ),
            'endDate' => $endDate->format( 'Y-m-d' ),
        ];

        foreach ( $ticketManagements as $ticketManagement )
        {
            $title = '#' . $ticketManagement->ticket->id;
            $title .= ' ' . ( $ticketManagement->executor ? $ticketManagement->executor->name : 'Не назначен' );
            $title .= ' ' . $ticketManagement->ticket->getAddress();
            $data[ 'events' ][] = [
                'url'       => route( 'tickets.show', $ticketManagement->getTicketNumber() ),
                'title'     => $title,
                'start'     => $ticketManagement->scheduled_begin->format( 'Y-m-d H:i:s' ),
                'end'       => $ticketManagement->scheduled_end->format( 'Y-m-d H:i:s' ),
            ];
        }

        return $data;

    }

    public function progress ( Request $request, $id )
    {
        $ticket = Ticket::find( $id );
        if ( $ticket )
        {
            return $ticket->getProgressData();
        }
    }

    public function owner ( Request $request )
    {

        if ( ! \Auth::user()->can( 'tickets.owner' ) )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Доступ запрещен' ] );
        }

        $ids = explode( ',', $request->get( 'ids', '' ) );
        if ( ! count( $ids ) )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявки не выбраны' ] );
        }

        $tickets = Ticket
            ::whereHas( 'managements', function ( $ticketManagements ) use ( $ids )
            {
                return $ticketManagements
                    ->mine()
                    ->whereIn( 'id', $ids );
            })
            ->whereNull( 'owner_id' )
            ->get();
        if ( ! $tickets->count() )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Заявки не найдены' ] );
        }

        foreach ( $tickets as $ticket )
        {
            $ticket->owner_id = \Auth::user()->id;
            $ticket->save();
        }

        \Cache::tags( 'tickets_counts' )->flush();

        return redirect()
            ->back()
            ->with( 'success', 'Готово' );

    }

    public function ownerCancel ( Request $request )
    {

        if ( ! \Auth::user()->can( 'tickets.owner' ) )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Доступ запрещен' ] );
        }

        $ids = explode( ',', $request->get( 'ids', '' ) );
        if ( ! count( $ids ) )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявки не выбраны' ] );
        }

        $tickets = Ticket
            ::whereHas( 'managements', function ( $ticketManagements ) use ( $ids )
            {
                return $ticketManagements
                    ->mine()
                    ->whereIn( 'id', $ids );
            })
            ->where( 'owner_id', '=', \Auth::user()->id )
            ->get();
        if ( ! $tickets->count() )
        {
            return redirect()
                ->back()
                ->withErrors( [ 'Заявки не найдены' ] );
        }

        foreach ( $tickets as $ticket )
        {
            $ticket->owner_id = null;
            $ticket->save();
        }

        \Cache::tags( 'tickets_counts' )->flush();

        return redirect()
            ->back()
            ->with( 'success', 'Готово' );

    }

    public function clearCache ()
    {
        \Cache::tags( 'tickets' )->flush();
        \Cache::tags( 'tickets_counts' )->flush();
        return redirect()->route( 'tickets.index' )->with( 'success', 'Кеш успешно сброшен' );
    }
	
}
