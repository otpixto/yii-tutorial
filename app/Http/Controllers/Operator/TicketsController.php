<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Jobs\SendStream;
use App\Models\Address;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Executor;
use App\Models\Management;
use App\Models\Region;
use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Models\Type;
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

    public function index ( Request $request, $statuses = [], $customer_id = null )
    {

        $field_operator = \Auth::user()->can( 'tickets.field_operator' );
        $field_management = \Auth::user()->can( 'tickets.field_management' );

        $types = null;

        if ( \Auth::user()->can( 'tickets.search' ) )
        {

            $availableStatuses = \Auth::user()->getAvailableStatuses( 'show', true, true );

            if ( ! count( $statuses ) && ! empty( $request->get( 'statuses' ) ) )
            {
                $statuses = explode( ',', $request->get( 'statuses' ) );
            }

            if ( \Cache::tags( [ 'catalog', 'tickets' ] )->has( 'types' ) )
            {
                $availableTypes = \Cache::tags( [ 'catalog', 'tickets' ] )->get( 'types' );
            }
            else
            {
                $res = Type::with( 'category' )->get()->sortBy( 'name' );
                $availableTypes = [];
                foreach ( $res as $r )
                {
                    $availableTypes[ $r->category->name ][ $r->id ] = $r->name;
                }
                \Cache::tags( [ 'catalog', 'tickets' ] )->put( 'types', $availableTypes, \Config::get( 'cache.time' ) );
            }

            $types = [];
            if ( ! empty( $request->get( 'types' ) ) )
            {
                $types = explode( ',', $request->get( 'types' ) );
            }

        }

        $ticketManagements = TicketManagement
            ::mine()
            ->whereHas( 'ticket', function ( $ticket ) use ( $request, $field_operator, $customer_id, $types )
            {

                if ( $customer_id )
                {
                    $ticket
                        ->whereHas( 'customer', function ( $q ) use ( $customer_id )
                        {
                            return $q
                                ->where( 'id', '=', $customer_id );
                        });
                }

                if ( ! empty( $request->get( 'group' ) ) )
                {
                    $ticket
                        ->where( Ticket::getTableName() . '.group_uuid', '=', $request->get( 'group' ) );
                }

                if ( \Auth::user()->can( 'tickets.search' ) )
                {

                    if ( count( $types ) )
                    {
                        $ticket
                            ->whereIn( Ticket::getTableName() . '.type_id', $types );
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
                                    ->where( Ticket::getTableName() . '.phone', 'like', $p )
                                    ->orWhere( Ticket::getTableName() . '.phone2', 'like', $p );
                            });
                    }

                    if ( ! empty( $request->get( 'firstname' ) ) )
                    {
                        $ticket
                            ->where( Ticket::getTableName() . '.firstname', 'like', '%' . str_replace( ' ', '%', $request->get( 'firstname' ) ) . '%' );
                    }

                    if ( ! empty( $request->get( 'middlename' ) ) )
                    {
                        $ticket
                            ->where( Ticket::getTableName() . '.middlename', 'like', '%' . str_replace( ' ', '%', $request->get( 'middlename' ) ) . '%' );
                    }

                    if ( ! empty( $request->get( 'lastname' ) ) )
                    {
                        $ticket
                            ->where( Ticket::getTableName() . '.lastname', 'like', '%' . str_replace( ' ', '%', $request->get( 'lastname' ) ) . '%' );
                    }

                    if ( ! empty( $request->get( 'emergency' ) ) )
                    {
                        $ticket
                            ->where( Ticket::getTableName() . '.emergency', '=', 1 );
                    }

                    if ( ! empty( $request->get( 'dobrodel' ) ) )
                    {
                        $ticket
                            ->where( Ticket::getTableName() . '.dobrodel', '=', 1 );
                    }

                    if ( ! empty( $request->get( 'from_lk' ) ) )
                    {
                        $ticket
                            ->where( Ticket::getTableName() . '.from_lk', '=', 1 );
                    }

                    if ( ! empty( $request->get( 'overdue_acceptance' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::getTableName() . '.deadline_acceptance < COALESCE( accepted_at, CURRENT_TIMESTAMP )' );
                    }

                    if ( ! empty( $request->get( 'overdue_execution' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::getTableName() . '.deadline_execution < COALESCE( completed_at, CURRENT_TIMESTAMP )' );
                    }

                }

                if ( ! empty( $request->get( 'ticket_id' ) ) )
                {
                    $ticket
                        ->where( Ticket::getTableName() . '.id', '=', $request->get( 'ticket_id' ) );
                }

                if ( ! empty( $request->get( 'period_from' ) ) )
                {
                    $ticket
                        ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $request->get( 'period_from' ) )->toDateTimeString() ] );
                }

                if ( ! empty( $request->get( 'period_to' ) ) )
                {
                    $ticket
                        ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $request->get( 'period_to' ) )->toDateTimeString() ] );
                }

                if ( $field_operator && !empty( $request->get( 'operator_id' ) ) )
                {
                    $ticket
                        ->where( Ticket::getTableName() . '.author_id', '=', $request->get( 'operator_id' ) );
                }

                if ( ! empty( $request->get( 'address_id' ) ) )
                {
                    $ticket
                        ->where( Ticket::getTableName() . '.address_id', '=', $request->get( 'address_id' ) );
                }

                if ( ! empty( $request->get( 'flat' ) ) )
                {
                    $ticket
                        ->where( Ticket::getTableName() . '.flat', '=', $request->get( 'flat' ) );
                }

                if ( ! empty( $request->get( 'actual_address_id' ) ) )
                {
                    $ticket
                        ->where( Ticket::getTableName() . '.actual_address_id', '=', $request->get( 'actual_address_id' ) );
                }

                if ( ! empty( $request->get( 'actual_flat' ) ) )
                {
                    $ticket
                        ->where( Ticket::getTableName() . '.actual_flat', '=', $request->get( 'actual_flat' ) );
                }

                if ( ! empty( $request->get( 'region_id' ) ) )
                {
                    $ticket
                        ->where( function ( $q ) use ( $request )
                        {
                            return $q
                                ->where( Ticket::getTableName() . '.region_id', '=', $request->get( 'region_id' ) )
                                ->orWhereHas( 'address', function ( $q2 ) use ( $request )
                                {
                                    return $q2
                                        ->where( 'region_id', '=', $request->get( 'region_id' ) );
                                });
                        });
                }

            });

        if ( count( $statuses ) )
        {
            $ticketManagements
                ->whereIn( TicketManagement::getTableName() . '.status_code', $statuses );
        }

        if ( ! empty( $request->get( 'rate' ) ) )
        {
            $ticketManagements
                ->where( TicketManagement::getTableName() . '.rate', '=', $request->get( 'rate' ) );
        }

        if ( $statuses )
        {
            $ticketManagements
                ->whereIn( TicketManagement::getTableName() . '.status_code', $statuses );
        }

        if ( ! empty( $request->get( 'address_id' ) ) )
        {
            $address = Address::where( 'id', $request->get( 'address_id' ) )->pluck( 'name', 'id' );
        }

        if ( ! empty( $request->get( 'actual_address_id' ) ) )
        {
            $actual_address = Address::where( 'id', $request->get( 'actual_address_id' ) )->pluck( 'name', 'id' );
        }

        if ( ! empty( $request->get( 'ticket_management_id' ) ) )
        {
            $ticketManagements
                ->where( TicketManagement::getTableName() . '.id', '=', $request->get( 'ticket_management_id' ) );
        }

        if ( ! empty( $request->get( 'management_id' ) ) )
        if ( ! empty( $request->get( 'management_id' ) ) )
        {
            $ticketManagements
                ->where( TicketManagement::getTableName() . '.management_id', '=', $request->get( 'management_id' ) );
        }

        if ( ! empty( $request->get( 'executor_id' ) ) )
        {
            $ticketManagements
                ->where( TicketManagement::getTableName() . '.executor_id', '=', $request->get( 'executor_id' ) );
        }

        switch ( $request->get( 'show' ) )
        {
            case 'call':
                $ticketManagements
                    ->select(
                        'tickets_managements.*',
                        'tickets.completed_at'
                    )
                    ->join( 'tickets', 'tickets.id', '=', 'tickets_managements.ticket_id' )
                    ->whereIn( TicketManagement::getTableName() . '.status_code', [ 'completed_with_act', 'completed_without_act', 'not_verified' ] )
                    ->orderBy( 'completed_at', 'asc' );
                break;
            case 'not_processed':
                $ticketManagements
                    ->whereIn( TicketManagement::getTableName() . '.status_code', [ 'transferred', 'transferred_again' ] )
                    ->orderBy( 'id', 'desc' );
                break;
            case 'not_completed':
                $ticketManagements
                    ->whereIn( TicketManagement::getTableName() . '.status_code', [ 'accepted', 'assigned', 'waiting' ] )
                    ->orderBy( 'id', 'desc' );
                break;
            default:
                $ticketManagements
                    ->orderBy( 'id', 'desc' );
                break;
        }

        if ( $request->get( 'export' ) == 1 && \Auth::user()->can( 'tickets.export' ) )
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
                    'Адрес проблемы'        => $ticket->address->name,
                    'Квартира'              => $ticket->flat,
                    'Проблемное место'      => $ticket->getPlace(),
                    'Категория заявки'      => $ticket->type->category->name,
                    'Тип заявки'            => $ticket->type->name,
                    'Текст обращения'       => $ticket->text,
                    'ФИО заявителя'         => $ticket->getName(),
                    'Телефон(ы) заявителя'  => $ticket->getPhones(),
                    'Адрес проживания'      => $ticket->customer ? $ticket->customer->getAddress() : '',
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
        }

        $ticketManagements = $ticketManagements
            ->with(
                'comments',
                'ticket',
                'ticket.type',
                'ticket.type.category',
				'ticket.address',
				'ticket.author',
                'management',
				'works'
            )
            ->paginate( 15 )
            ->appends( $request->all() );

        $regions = Region
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );


        if ( \Auth::user()->can( 'tickets.search' ) )
        {

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

        }

        return view( 'tickets.index' )
            ->with( 'ticketManagements', $ticketManagements )
            ->with( 'availableTypes', $availableTypes ?? [] )
            ->with( 'types', $types ?? [] )
            ->with( 'field_operator', $field_operator ?? false )
            ->with( 'field_management', $field_management ?? false )
            ->with( 'regions', $regions ?? [] )
            ->with( 'address', $address ?? [] )
            ->with( 'actual_address', $actual_address ?? [] )
            ->with( 'availableStatuses', $availableStatuses ?? [] )
            ->with( 'statuses', $statuses ?? [] );

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
            ::orderBy( 'name' )
            ->get();

        $types = [];
        foreach ( $res as $r )
        {
            $types[ $r->category->name ][ $r->id ] = $r->name;
        }

        $regions = Region
            ::mine()
            ->current()
            ->orderBy( 'name' )
            ->pluck( 'name', 'id' );

        return view( 'tickets.create' )
            ->with( 'types', $types )
            ->with( 'draft', $draft )
            ->with( 'regions', $regions )
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

        return redirect()
            ->route( 'tickets.show', $managements_count == 1 ? $ticketManagement->getTicketNumber() : $ticket->id )
            ->with( 'success', 'Заявка успешно добавлена' );

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
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

        return view( 'tickets.show' )
            ->with( 'ticket', $ticket )
            ->with( 'ticketManagement', $ticketManagement ?? null )
            ->with( 'availableStatuses', $availableStatuses )
            ->with( 'ticketCalls', $ticketCalls )
            ->with( 'comments', $comments )
            ->with( 'dt_now', Carbon::now() )
            ->with( 'execution_hours', $execution_hours ?? null );

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $ticket_id
     * @param  int  $ticket_management_id
     * @return \Illuminate\Http\Response
     */
    public function open ( Request $request, $ticket_id, $ticket_management_id = null )
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

        return view( 'tickets.show' )
            ->with( 'ticket', $ticket )
            ->with( 'ticketManagement', $ticketManagement ?? null )
            ->with( 'availableStatuses', $availableStatuses )
            ->with( 'ticketCalls', $ticketCalls )
            ->with( 'comments', $comments )
            ->with( 'dt_now', Carbon::now() );

    }

    public function saveWork ( Request $request, $ticket_id, $ticket_management_id = null )
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

        $rules = [
            'works.*.name'				        => 'required|string',
            'works.*.quantity'				    => 'required|numeric|min:1',
        ];

        $this->validate( $request, $rules );

        $res = $ticketManagement->saveWorks( $request->get( 'works', [] ) );
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
				
			case 'address':
			
				return view( 'tickets.edit.address' )
					->with( 'ticket', $ticket )
					->with( 'param', $param );
			
				break;

            case 'actual_address':

                return view( 'tickets.edit.actual_address' )
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
        }

        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

        \DB::commit();

        return redirect()->back()->with( 'success', 'Статус изменен' );

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

    public function customerTickets ( Request $request, $customer_id )
    {

        if ( ! \Auth::user()->can( 'tickets.customer_tickets' ) )
        {
            return redirect()->back()->withErrors( [ 'Доступ запрещен' ] );
        }

        Title::add( 'Заявки заявителя' );

        return $this->index( $request, null, $customer_id );

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

        $works = $ticketManagement->works;

        $lines = 5 - $works->count();
        if ( $lines < 0 ) $lines = 0;

        return view( 'tickets.act' )
            ->with( 'ticketManagement', $ticketManagement )
            ->with( 'works', $works )
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
			::whereNotIn( 'id', $ticket->managements->pluck( 'management_id' ) )
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

    public function getExecutorForm ( Request $request )
    {
        $ticketManagement = TicketManagement::find( $request->get( 'id' ) );
        if ( ! $ticketManagement )
        {
            return view( 'parts.error' )
                ->with( 'error', 'Заявка не найдена' );
        }
        $management = $ticketManagement->management;
        $executors = [ null => 'Выбрать из списка' ] + $management->executors->pluck( 'name', 'id' )->toArray();
        return view( 'parts.executor_form' )
            ->with( 'ticketManagement', $ticketManagement )
            ->with( 'management', $management )
            ->with( 'executors', $executors );
    }

    public function postExecutorForm ( Request $request )
    {
        $this->validate( $request, [
            'executor_id'       => 'required_without:executor_name|nullable|integer',
            'executor_name'     => 'required_without:executor_id|nullable',
        ]);
        $ticketManagement = TicketManagement::find( $request->get( 'id' ) );
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

    public function getRateForm ( Request $request )
    {
        $ticketManagement = TicketManagement::find( $request->get( 'id' ) );
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

    public function postRateForm ( Request $request )
    {
        $ticketManagement = TicketManagement::find( $request->get( 'id' ) );
        if ( ! $ticketManagement )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }
        \DB::beginTransaction();
        $ticketManagement->rate = $request->get( 'rate' );
        $ticketManagement->rate_comment = $request->get( 'comment', null );
        if ( $request->get( 'closed_with_confirm' ) == 1 && $ticketManagement->status_code != 'closed_with_confirm' )
        {
            $res = $ticketManagement->changeStatus( 'closed_with_confirm', true );
            if ( $res instanceof MessageBag )
            {
                return redirect()->back()->withErrors( $res );
            }
        }
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
            return $res;
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
            ::where( 'phone', '=', $phone )
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

        return redirect()
            ->route( 'tickets.index', $data );

    }

    public function clearCache ()
    {
        \Cache::tags( 'tickets' )->flush();
        return redirect()->route( 'tickets.index' )->with( 'success', 'Кеш успешно сброшен' );
    }
	
}
