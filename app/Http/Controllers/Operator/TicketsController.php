<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
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

            $types = [];
            $managements = [];
            $operators = [];
            $statuses = [];

            $ticketManagements = TicketManagement
                ::mine();

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

                $ticketManagements->whereHas( 'ticket', function ( $ticket ) use ( $request, $types, $operators )
                {

                    if ( $request->get( 'customer_id' ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.customer_id', '=', $request->get( 'customer_id' ) );
                    }

                    if ( ! empty( $request->get( 'group' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.group_uuid', '=', $request->get( 'group' ) );
                    }

                    if ( \Auth::user()->can( 'tickets.search' ) )
                    {

                        if ( count( $types ) )
                        {
                            $ticket
                                ->whereIn( Ticket::$_table . '.type_id', $types );
                        }

                        if ( count( $operators ) )
                        {
                            $ticket
                                ->whereIn( Ticket::$_table . '.author_id', $operators );
                        }

                        if ( ! empty( $request->get( 'phone' ) ) )
                        {
                            $p = str_replace( '+7', '', $request->get( 'phone' ) );
                            $p = preg_replace( '/[^0-9_]/', '', $p );
                            $p = '%' . mb_substr( $p, - 10 ) . '%';
                            $ticket
                                ->where( function ( $q ) use ( $p )
                                {
                                    return $q
                                        ->where( Ticket::$_table . '.phone', 'like', $p )
                                        ->orWhere( Ticket::$_table . '.phone2', 'like', $p );
                                });
                        }

                        if ( ! empty( $request->get( 'firstname' ) ) )
                        {
                            $ticket
                                ->where( Ticket::$_table . '.firstname', 'like', '%' . str_replace( ' ', '%', $request->get( 'firstname' ) ) . '%' );
                        }

                        if ( ! empty( $request->get( 'middlename' ) ) )
                        {
                            $ticket
                                ->where( Ticket::$_table . '.middlename', 'like', '%' . str_replace( ' ', '%', $request->get( 'middlename' ) ) . '%' );
                        }

                        if ( ! empty( $request->get( 'lastname' ) ) )
                        {
                            $ticket
                                ->where( Ticket::$_table . '.lastname', 'like', '%' . str_replace( ' ', '%', $request->get( 'lastname' ) ) . '%' );
                        }

                        if ( ! empty( $request->get( 'emergency' ) ) )
                        {
                            $ticket
                                ->where( Ticket::$_table . '.emergency', '=', 1 );
                        }

                        if ( ! empty( $request->get( 'dobrodel' ) ) )
                        {
                            $ticket
                                ->where( Ticket::$_table . '.dobrodel', '=', 1 );
                        }

                        if ( ! empty( $request->get( 'from_lk' ) ) )
                        {
                            $ticket
                                ->where( Ticket::$_table . '.from_lk', '=', 1 );
                        }

                        if ( ! empty( $request->get( 'overdue_acceptance' ) ) )
                        {
                            $ticket
                                ->whereRaw( Ticket::$_table . '.deadline_acceptance < COALESCE( accepted_at, CURRENT_TIMESTAMP )' );
                        }

                        if ( ! empty( $request->get( 'overdue_execution' ) ) )
                        {
                            $ticket
                                ->whereRaw( Ticket::$_table . '.deadline_execution < COALESCE( completed_at, CURRENT_TIMESTAMP )' );
                        }

                    }

                    if ( ! empty( $request->get( 'ticket_id' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.id', '=', $request->get( 'ticket_id' ) );
                    }

                    if ( ! empty( $request->get( 'created_from' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.created_at >= ?', [ Carbon::parse( $request->get( 'created_from' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'created_to' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.created_at <= ?', [ Carbon::parse( $request->get( 'created_to' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'accepted_from' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.accepted_at >= ?', [ Carbon::parse( $request->get( 'accepted_from' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'accepted_to' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.accepted_at <= ?', [ Carbon::parse( $request->get( 'accepted_to' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'completed_from' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.completed_at >= ?', [ Carbon::parse( $request->get( 'completed_from' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'completed_to' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.completed_at <= ?', [ Carbon::parse( $request->get( 'completed_to' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'postponed_from' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.postponed_at >= ?', [ Carbon::parse( $request->get( 'postponed_from' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'postponed_to' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.postponed_at <= ?', [ Carbon::parse( $request->get( 'postponed_to' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'operator_id' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.author_id', '=', $request->get( 'operator_id' ) );
                    }

                    if ( ! empty( $request->get( 'building_id' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.building_id', '=', $request->get( 'building_id' ) );
                    }

                    if ( ! empty( $request->get( 'segment_id' ) ) )
                    {
                        $ticket
                            ->whereHas( 'building', function ( $building ) use ( $request )
                            {
                                return $building
                                    ->where( Building::$_table . '.segment_id', '=', $request->get( 'segment_id' ) );
                            });
                    }

                    if ( ! empty( $request->get( 'flat' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.flat', '=', $request->get( 'flat' ) );
                    }

                    if ( ! empty( $request->get( 'actual_building_id' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.actual_building_id', '=', $request->get( 'actual_building_id' ) );
                    }

                    if ( ! empty( $request->get( 'actual_flat' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.actual_flat', '=', $request->get( 'actual_flat' ) );
                    }

                    if ( ! empty( $request->get( 'provider_id' ) ) )
                    {
                        $ticket
                            ->where( function ( $q ) use ( $request )
                            {
                                return $q
                                    ->where( Ticket::$_table . '.provider_id', '=', $request->get( 'provider_id' ) );
                            });
                    }

                    if ( $request->get( 'show' ) == 'overdue' )
                    {
                        $ticket
                            ->overdue();
                    }

                });

                if ( count( $statuses ) )
                {
                    $ticketManagements
                        ->whereIn( TicketManagement::$_table . '.status_code', $statuses );
                }

                if ( count( $managements ) )
                {
                    $ticketManagements
                        ->whereIn( TicketManagement::$_table . '.management_id', $managements );
                }

                if ( ! empty( $request->get( 'rate' ) ) )
                {
                    $ticketManagements
                        ->where( TicketManagement::$_table . '.rate', '=', $request->get( 'rate' ) );
                }

                if ( ! empty( $request->get( 'ticket_management_id' ) ) )
                {
                    $ticketManagements
                        ->where( TicketManagement::$_table . '.id', '=', $request->get( 'ticket_management_id' ) );
                }

                if ( ! empty( $request->get( 'executor_id' ) ) )
                {
                    $ticketManagements
                        ->where( TicketManagement::$_table . '.executor_id', '=', $request->get( 'executor_id' ) );
                }

            }

            switch ( $request->get( 'show' ) )
            {
                case 'call':
                    $ticketManagements
                        ->select(
                            TicketManagement::$_table . '.*',
                            Ticket::$_table . '.completed_at'
                        )
                        ->join( Ticket::$_table, Ticket::$_table . '.id', '=', TicketManagement::$_table . '.ticket_id' )
                        ->whereIn( TicketManagement::$_table . '.status_code', [ 'completed_with_act', 'completed_without_act', 'not_verified' ] )
                        ->orderBy( Ticket::$_table . '.completed_at', 'asc' );
                    break;
                case 'not_processed':
                    $ticketManagements
                        ->notProcessed()
                        ->orderBy( TicketManagement::$_table . '.ticket_id', 'desc' );
                    break;
                case 'in_process':
                    $ticketManagements
                        ->inProcess()
                        ->orderBy( TicketManagement::$_table . '.ticket_id', 'desc' );
                    break;
                case 'completed':
                    $ticketManagements
                        ->completed()
                        ->orderBy( TicketManagement::$_table . '.ticket_id', 'desc' );
                    break;
                case 'closed':
                    $ticketManagements
                        ->closed()
                        ->orderBy( TicketManagement::$_table . '.ticket_id', 'desc' );
                    break;
                default:
                    $ticketManagements
                        ->orderBy( TicketManagement::$_table . '.ticket_id', 'desc' );
                    break;
            }

            /*if ( $request->get( 'export' ) == 1 && \Auth::user()->can( 'tickets.export' ) && $ticketManagements->count() && $ticketManagements->count() < 1000 )
            {
                $ticketManagements = $ticketManagements->get();
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
                        'Категория заявки'      => $ticket->type->category->name,
                        'Классификатор'         => $ticket->type->name,
                        'Текст обращения'       => $ticket->text,
                        'ФИО заявителя'         => $ticket->getName(),
                        'Телефон(ы) заявителя'  => $ticket->getPhones(),
                    ];
                    if ( $field_operator )
                    {
                        $data[ $i ][ 'Оператор' ] = $ticket->author->getName();
                    }
                    if ( $field_management )
                    {
                        $data[ $i ][ 'ЭО' ] = $ticketManagement->management->name;
                    }
                    $i ++;
                }
                \Excel::create( 'ЗАЯВКИ', function ( $excel ) use ( $data )
                {
                    $excel->sheet( 'ЗАЯВКИ', function ( $sheet ) use ( $data )
                    {
                        $sheet->fromArray( $data );
                    });
                })->export( 'xls' );
                die;
            }*/

            $ticketManagements = $ticketManagements
                ->with(
                    'ticket',
                    'ticket.type',
                    'ticket.type.category',
                    'ticket.building',
                    'management'
                )
                ->paginate( config( 'pagination.per_page' ) )
                ->appends( $request->all() );

            return view( 'tickets.parts.list' )
                ->with( 'ticketManagements', $ticketManagements );

        }

        return view( 'tickets.index' )
            ->with( 'request', $request );

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

        $availableStatuses = \Auth::user()->getAvailableStatuses( 'show', true, true );
        $res = Type::mine()->with( 'category' )->get()->sortBy( 'name' );
        $availableTypes = [];
        foreach ( $res as $r )
        {
            $availableTypes[ $r->category->name ][ $r->id ] = $r->name;
        }

        if ( \Auth::user()->can( 'tickets.field_operator' ) )
        {
            if ( \Cache::tags( [ 'users', 'ticket' ] )->has( 'operators' ) )
            {
                $availableOperators = \Cache::tags( [ 'users', 'ticket' ] )->get( 'operators' );
            }
            else
            {
                $res = User::role( 'operator' )->get();
                $availableOperators = [];
                foreach ( $res as $r )
                {
                    $availableOperators[ $r->id ] = $r->getName();
                }
                asort( $availableOperators );
                \Cache::tags( [ 'users', 'ticket' ] )->put( 'operators', $availableOperators, \Config::get( 'cache.time' ) );
            }
        }

        if ( \Auth::user()->can( 'tickets.field_management' ) )
        {
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
        }

        if ( ! count( $types ) )
        {
            foreach ( $availableTypes as $category )
            {
                $types = array_merge( $types, array_keys( $category ) );
            }
        }

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
            ->with( 'building', $building ?? [] )
            ->with( 'segment', $segment ?? [] )
            ->with( 'actual_building', $actual_building ?? [] )
            ->with( 'statuses', $statuses ?? [] );

    }

    public function customersTickets ( Request $request, $id )
    {

        $ticket = Ticket::mine()->find( $id );
        if ( ! $ticket )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Произошла ошибка. Заявка не найдена' );
        }

        $tickets = $ticket->customerTickets()
            ->mine()
            ->orderBy( 'id', 'desc' )
            ->paginate( 15 );

        return view( 'tickets.mini_table' )
            ->with( 'tickets', $tickets )
            ->with( 'link', route( 'tickets.index', [ 'phone' => $ticket->phone ] ) );

    }

    public function neighborsTickets ( Request $request, $id )
    {

        $ticket = Ticket::mine()->find( $id );
        if ( ! $ticket )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Произошла ошибка. Заявка не найдена' );
        }

        $tickets = $ticket->neighborsTickets()
            ->mine()
            ->where( 'phone', '!=', $ticket->phone )
            ->orderBy( 'id', 'desc' )
            ->paginate( 15 );

        return view( 'tickets.mini_table' )
            ->with( 'tickets', $tickets )
            ->with( 'link', route( 'tickets.index', [ 'building_id' => $ticket->building_id ] ) );

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

        return view( 'parts.ticket' )
            ->with( 'ticketManagement', $ticketManagement )
            ->with( 'ticket', $ticketManagement->ticket )
            ->with( 'field_operator', $field_operator )
            ->with( 'field_management', $field_management )
            ->with( 'hide', $hide )
            ->with( 'hideComments', $request->get( 'hideComments', false ) );

    }

    public function comments ( Request $request, $id )
    {

        $ticket = Ticket
            ::mine()
            ->where( 'id', '=', $id )
            ->first();

        if ( ! $ticket ) return;

        if ( $request->get( 'commentsOnly', false ) )
        {
            return view( 'parts.comments' )
                ->with( 'origin', $ticket )
                ->with( 'comments', $ticket->getComments() );
        }
        else
        {
            return view( 'parts.ticket_comments' )
                ->with( 'ticket', $ticket )
                ->with( 'comments', $ticket->getComments() );
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ( Request $request )
    {

        $draft = Ticket::create();

        if ( $draft instanceof MessageBag )
        {
            return redirect()->route( 'tickets.index' )->withErrors( $draft );
        }

        //Title::add( 'Добавить заявку' );
        Title::add( 'Заявка #' . $draft->id . ' от ' . $draft->created_at->format( 'd.m.Y H:i' ) );

        $res = Type
            ::mine()
            ->orderBy( Type::$_table . '.name' )
            ->get();

        $types = [];
        foreach ( $res as $r )
        {
            $name = $r->name;
            if ( $r->is_pay )
            {
                $name .= ' (платно)';
            }
            $types[ $r->category->name ][ $r->id ] = $name;
        }

        $providers = Provider
            ::mine()
            ->current()
            ->orderBy( Provider::$_table . '.name' )
            ->pluck( Provider::$_table . '.name', 'id' );

        return view( 'tickets.create' )
            ->with( 'types', $types )
            ->with( 'draft', $draft )
            ->with( 'providers', $providers )
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

        $this->validate( $request, Ticket::$rules );
        $this->validate( $request, Customer::$rules );

        if ( ! isset( Ticket::$places[ $request->get( 'place_id' ) ] ) )
        {
            return redirect()->back()->withErrors( [ 'Некорректное проблемное место' ] );
        }

		\DB::beginTransaction();

        $draft = Ticket
            ::draft()
            ->first();

        if ( $draft )
        {
            $ticket = $draft;
            $ticket->created_at = Carbon::now()->toDateTimeString();
            $ticket->edit( $request->all() );
        }
        else
        {
            $ticket = Ticket::create( $request->all() );
        }

        if ( $ticket instanceof MessageBag )
        {
            return redirect()->back()->withErrors( $ticket );
        }

        if ( $ticket->customer )
        {
            $ticket->customer->edit( $request->all() );
        }
        else
        {
            $customer = Customer::create( $request->all() );
            $customer->save();
        }

        $status_code = 'no_contract';
        $managements = $request->get( 'managements', [] );
        $managements_count = 0;

        foreach ( $managements as $manament_id )
        {

            $ticketManagement = TicketManagement::create([
                'ticket_id'         => $ticket->id,
                'management_id'     => $manament_id,
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
                $status_code = 'created';
                $res = $ticketManagement->changeStatus( 'created', true );
                if ( $res instanceof MessageBag )
                {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors( $res );
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

		if ( count( $request->get( 'tags', [] ) ) )
		{
			$tags = explode( ',', $request->get( 'tags' ) );
			foreach ( $tags as $tag )
			{
				$ticket->addTag( $tag );
			}
		}

		$res = $ticket->changeStatus( $status_code, true );

        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( $res );
        }

		\DB::commit();

        \Cache::tags( 'tickets_counts' )->flush();

        return redirect()
            ->route( 'tickets.show', $managements_count == 1 ? $ticketManagement->getTicketNumber() : $ticket->id )
            ->with( 'success', 'Заявка успешно добавлена' );

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

        $ticket = Ticket
            ::mine()
            ->find( $ticket_id );

        if ( ! $ticket )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }

        $comments = $ticket->getComments();

        if ( $ticket_management_id )
        {
            $ticketManagement = $ticket
                ->managements()
                ->find( $ticket_management_id );
            if ( ! $ticketManagement )
            {
                return redirect()
                    ->route( 'tickets.index' )
                    ->withErrors( [ 'Заявка не найдена' ] );
            }
            Title::add( 'Заявка #' . $ticketManagement->getTicketNumber() . ' от ' . $ticketManagement->ticket->created_at->format( 'd.m.Y H:i' ) );
        }
        else
        {
            Title::add( 'Заявка #' . $ticket->id . ' от ' . $ticket->created_at->format( 'd.m.Y H:i' ) );
        }

        if ( \Auth::user()->can( 'calls.all' ) && $ticket->calls->count() )
        {
            $ticketCalls = $ticket->calls()->actual()->get();
        }
        else if ( \Auth::user()->can( 'calls.my' ) && $ticket->calls()->actual()->mine()->count() )
        {
            $ticketCalls = $ticket->calls()->actual()->mine()->get();
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

        if ( isset( $ticketManagement ) )
        {
            $model_name = get_class( $ticketManagement );
            $model_id = $ticketManagement->id;
            $url = route( 'tickets.status', $ticketManagement->getTicketNumber() );
            foreach ( $ticketManagement->getAvailableStatuses( 'edit', true, true ) as $status_code => $status_name )
            {
                $availableStatuses[ $status_code ] = compact( 'status_name', 'model_name', 'model_id', 'url' );
            }
        }

        return view( $request->ajax() ? 'tickets.parts.info' : 'tickets.show' )
            ->with( 'ticket', $ticket )
            ->with( 'ticketManagement', $ticketManagement ?? null )
            ->with( 'availableStatuses', $availableStatuses )
            ->with( 'ticketCalls', $ticketCalls )
            ->with( 'comments', $comments )
            ->with( 'dt_now', Carbon::now() );

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

    public function history ( Request $request, $ticket_id, $ticket_management_id )
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

        $ticketManagement = $ticket
            ->managements()
            ->find( $ticket_management_id );
        if ( ! $ticketManagement )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }

        Title::add( 'История изменений заявки #' . $ticketManagement->getTicketNumber() . ' от ' . $ticketManagement->ticket->created_at->format( 'd.m.Y H:i' ) );

        $statuses = $ticketManagement->statusesHistory->sortBy( 'id' );
        $logs = $ticketManagement->logs->merge( $ticket->logs )->sortBy( 'id' );

        return view( 'tickets.history' )
            ->with( 'ticket', $ticket )
            ->with( 'ticketManagement', $ticketManagement )
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
		
		switch ( $param )
		{
			
			case 'type':
			
				$res = Type
					::orderBy( 'name' )
					->get();
			
				$types = [];
				foreach ( $res as $r )
				{
					$types[ $r->category->name ][ $r->id ] = $r->name;
				}
			
				return view( 'tickets.edit.type' )
					->with( 'ticket', $ticket )
					->with( 'types', $types )
					->with( 'param', $param );
			
				break;
				
			case 'building':
			
				return view( 'tickets.edit.building' )
					->with( 'ticket', $ticket )
					->with( 'param', $param );
			
				break;

            case 'actual_building':

                return view( 'tickets.edit.actual_building' )
                    ->with( 'ticket', $ticket )
                    ->with( 'param', $param );

                break;
				
			case 'mark':
			
				return view( 'tickets.edit.mark' )
					->with( 'ticket', $ticket )
					->with( 'param', $param );
			
				break;
				
			case 'text':
			
				return view( 'tickets.edit.text' )
					->with( 'ticket', $ticket )
					->with( 'param', $param );
			
				break;
				
			case 'name':
			
				return view( 'tickets.edit.name' )
					->with( 'ticket', $ticket )
					->with( 'param', $param );
			
				break;
				
			case 'phone':
			
				return view( 'tickets.edit.phone' )
					->with( 'ticket', $ticket )
					->with( 'param', $param );
			
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
		
		$ticket->edit( $request->all() );

        $this->dispatch( new SendStream( 'update', $ticket ) );

        $success = 'Заявка успешно отредактирована';

        if ( $request->ajax() )
        {
            return compact( 'success' );
        }
        else
        {
            return redirect()
                ->route( 'tickets.show', $ticket->id )
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

        if ( $ticket_management_id )
        {
            $ticketManagement = $ticket
                ->managements()
                ->find( $ticket_management_id );
            if ( ! $ticketManagement )
            {
                return redirect()
                    ->route( 'tickets.index' )
                    ->withErrors( [ 'Заявка не найдена' ] );
            }
            $res = $ticketManagement->changeStatus( $request->get( 'status_code' ) );
            if ( ! empty( $request->get( 'comment' ) ) )
            {
                $res = $ticketManagement->addComment( $request->get( 'comment' ) );
                if ( $res instanceof MessageBag )
                {
                    return redirect()->back()
                        ->withErrors( $res );
                }
            }
            if ( ! empty( $request->get( 'postponed_to' ) ) )
            {
                $ticketManagement->ticket->postponed_to = Carbon::parse( $request->get( 'postponed_to' ) )->toDateString();
                if ( ! empty( $request->get( 'postponed_comment' ) ) )
                {
                    $ticketManagement->ticket->postponed_comment = $request->get( 'postponed_comment' );
                }
                $ticketManagement->ticket->save();
            }
        }
        else
        {
            $res = $ticket->changeStatus( $request->get( 'status_code' ) );
            if ( ! empty( $request->get( 'comment' ) ) )
            {
                $res = $ticket->addComment( $request->get( 'comment' ) );
                if ( $res instanceof MessageBag )
                {
                    return redirect()->back()
                        ->withErrors( $res );
                }
            }
            if ( ! empty( $request->get( 'postponed_to' ) ) )
            {
                $ticket->postponed_to = Carbon::parse( $request->get( 'postponed_to' ) )->toDateString();
                if ( ! empty( $request->get( 'postponed_comment' ) ) )
                {
                    $ticket->postponed_comment = $request->get( 'postponed_comment' );
                }
                $ticket->save();
            }
        }

        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

        \DB::commit();

        \Cache::tags( 'tickets_counts' )->flush();

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
            ::mine()
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

        $services = $ticketManagement->services;

        $lines = 10 - $services->count();
        if ( $lines < 0 ) $lines = 0;

        $total = $services->sum( function ( $service ){ return $service[ 'amount' ] * $service[ 'quantity' ]; } );

        $ticketManagement->addLog( 'Распечатал акт №' . $ticketManagement->getTicketNumber() );

        return view( 'tickets.act' )
            ->with( 'ticketManagement', $ticketManagement )
            ->with( 'services', $services )
            ->with( 'total', $total )
            ->with( 'lines', $lines );

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
	
	public function getAddManagement ( Request $request, $id )
    {
        $ticket = Ticket::find( $id );
		$managements = Management
			::whereNotIn( 'id', $ticket->managements()->pluck( 'management_id' ) )
			->where( 'has_contract', '=', 1 )
			->get();
        return view( 'tickets.edit.add_management' )
            ->with( 'ticket', $ticket )
			->with( 'managements', $managements );
    }
	
	public function postAddManagement ( Request $request, $id )
    {
        $ticket = Ticket::find( $request->get( 'id' ) );
		if ( ! $ticket )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }
		$management_id = $request->get( 'management_id' );
		if ( ! $management_id )
		{
			return redirect()
                ->route( 'tickets.show', $ticket->id )
                ->withErrors( [ 'ЭО не выбрана' ] );
		}
        $ticketManagement = TicketManagement::create([
			'ticket_id'         => $ticket->id,
			'management_id'     => $request->get( 'management_id' ),
		]);
		return redirect()
            ->route( 'tickets.show', $ticket->id )
            ->with( 'success', 'ЭО успешно добавлена' );
    }
	
	public function postDelManagement ( Request $request )
    {
        $ticketManagement = TicketManagement::find( $request->get( 'id' ) );
		$ticketManagement->delete();
    }

    public function getExecutorForm ( Request $request, $id )
    {

        $ticketManagement = TicketManagement::find( $id );
        if ( ! $ticketManagement )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Заявка не найдена' );
        }

        $management = $ticketManagement->management;
        $executors = [ null => 'Выбрать из списка' ] + $management->executors()->pluck( 'name', 'id' )->toArray();
		
        return view( 'parts.executor_form' )
            ->with( 'ticketManagement', $ticketManagement )
            ->with( 'management', $management )
            ->with( 'executors', $executors );
			
    }

    public function postExecutorForm ( Request $request, $id )
    {
        $this->validate( $request, [
            'executor_id'       => 'required_without:executor_name|nullable|integer',
            'executor_name'     => 'required_without:executor_id|nullable',
        ]);
        $ticketManagement = TicketManagement::find( $id );
        if ( ! $ticketManagement )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }
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
            $executor = $ticketManagement->management->executors()->where( 'name', '=', $request->get( 'executor_name' ) )->first();
            if ( ! $executor )
            {
                $executor = Executor::create([
                    'management_id'     => $ticketManagement->management->id,
                    'name'              => $request->get( 'executor_name' )
                ]);
                if ( $executor instanceof MessageBag )
                {
                    return redirect()
                        ->back()
                        ->withErrors( $executor );
                }
                $executor->save();

            }
        }
        $ticketManagement->executor_id = $executor->id;
        $ticketManagement->save();
        $res = $ticketManagement->changeStatus( 'assigned', true );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }
        $res = $ticketManagement->addLog( 'Назначен исполнитель "' . $executor->name . '"' );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }
        \DB::commit();
        return redirect()->back()->with( 'success', 'Исполнитель успешно назначен' );
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
        return view( 'parts.rate_form' )
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

    public function postSave ( Request $request )
    {
        $ticket = Ticket::find( $request->id );
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

    }

    public function addTag ( Request $request )
    {
        $ticket = Ticket::find( $request->id );
        if ( ! $ticket ) return;
        $tag = trim( $request->get( 'tag', '' ) );
        if ( empty( $tag ) || $ticket->tags()->where( 'text', '=', $tag )->count() ) return;
        $ticket->addTag( $tag );
    }

    public function delTag ( Request $request )
    {
        $ticket = Ticket::find( $request->id );
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
        $ticket->delete();
        return redirect()
            ->route( 'tickets.index' );
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
            ::mine()
            ->where( 'phone', '=', $phone )
            ->where( 'status_code', '!=', 'draft' )
            ->orderBy( 'id', 'desc' )
            ->take( 10 )
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

        return redirect()->route( 'tickets.index', $data );

    }

    public function calendar ( Request $request, $date )
    {
        Title::add( 'Календарь' );
        $beginDate = Carbon::parse( $date )->startOfMonth();
        $endDate = Carbon::parse( $date )->endOfMonth();
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
            ->with( 'date', Carbon::parse ( $date ) )
            ->with( 'beginDate', $beginDate )
            ->with( 'endDate', $endDate )
            ->with( 'availableManagements', $availableManagements );
    }

    public function calendarData ( Request $request )
    {

        $date = Carbon::parse ( $request->get( 'date' ) );
        $beginDate = Carbon::parse( $date )->startOfMonth();
        $endDate = Carbon::parse( $date )->endOfMonth();

        $ticketManagements = TicketManagement
            ::mine()
            ->whereHas( 'ticket', function ( $ticket ) use ( $request )
            {

                $ticket->inProcess();

                if ( $request->get( 'building_id' ) )
                {
                    $ticket
                        ->where( Ticket::$_table . '.building_id', $request->get( 'building_id' ) );
                }

                if ( $request->get( 'segment_id' ) )
                {
                    $ticket
                        ->whereHas( 'building', function ( $building ) use ( $request )
                        {
                            return $building
                                ->where( Building::$_table . '.segment_id', $request->get( 'segment_id' ) );
                        });
                }

            })
            ->whereNotNull( TicketManagement::$_table . '.scheduled_end' )
            ->whereBetween( TicketManagement::$_table . '.scheduled_begin', [ $beginDate->toDateTimeString(), $endDate->toDateTimeString() ] );

        if ( count( $request->get( 'managements', [] ) ) )
        {
            $ticketManagements
                ->whereIn( TicketManagement::$_table . '.management_id', $request->get( 'managements', [] ) );
        }

        $ticketManagements = $ticketManagements->get();

        $data = [
            'events' => []
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

    public function clearCache ()
    {
        \Cache::tags( 'tickets' )->flush();
        \Cache::tags( 'tickets_counts' )->flush();
        return redirect()->route( 'tickets.index' )->with( 'success', 'Кеш успешно сброшен' );
    }
	
}
