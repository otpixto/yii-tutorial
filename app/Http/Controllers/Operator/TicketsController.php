<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Models\Address;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Management;
use App\Models\Region;
use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Models\Type;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Ramsey\Uuid\Uuid;

class TicketsController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
        Title::add( 'Реестр заявок' );
    }

    public function index ( Request $request, $statuses = null )
    {

        $field_operator = \Auth::user()->can( 'tickets.field_operator' );
        $field_management = \Auth::user()->can( 'tickets.field_management' );

        $exp_number = explode( '/', $request->get( 'id', '' ) );

        $ticketManagements = TicketManagement
            ::mine()
            ->whereHas( 'ticket', function ( $ticket ) use ( $request, $field_operator, $exp_number )
            {

                if ( !empty( $request->get( 'search' ) ) )
                {
                    $ticket
                        ->fastSearch( $request->get( 'search' ) );
                }

                if ( !empty( $request->get( 'group' ) ) )
                {
                    $ticket
                        ->where( 'group_uuid', '=', $request->get( 'group' ) );
                }

                if ( !empty( $exp_number[0] ) )
                {
                    $ticket
                        ->where( 'id', '=', $exp_number[0] );
                }

                if ( !empty( $request->get( 'period_from' ) ) )
                {
                    $ticket
                        ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( $request->get( 'period_from' ) )->toDateTimeString() ] );
                }

                if ( !empty( $request->get( 'period_to' ) ) )
                {
                    $ticket
                        ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( $request->get( 'period_to' ) )->toDateTimeString() ] );
                }

                if ( $field_operator && !empty( $request->get( 'operator_id' ) ) )
                {
                    $ticket
                        ->where( 'author_id', '=', $request->get( 'operator_id' ) );
                }

                if ( !empty( $request->get( 'category_id' ) ) )
                {
                    $ticket
                        ->whereHas( 'type', function ( $q ) use ( $request )
                        {
                            return $q
                                ->where( 'category_id', '=', $request->get( 'category_id' ) );
                        });
                }

                if ( !empty( $request->get( 'address_id' ) ) )
                {
                    $ticket
                        ->where( 'address_id', '=', $request->get( 'address_id' ) );
                }

                if ( !empty( $request->get( 'flat' ) ) )
                {
                    $ticket
                        ->where( 'flat', '=', $request->get( 'flat' ) );
                }

                if ( !empty( $request->get( 'emergency' ) ) )
                {
                    $ticket
                        ->where( 'emergency', '=', 1 );
                }

            });
			
		if ( !empty( $request->get( 'status_code' ) ) )
		{
			$ticketManagements
				->where( 'status_code', '=', $request->get( 'status_code' ) );
		}

        if ( !empty( $request->get( 'rate' ) ) )
        {
            $ticketManagements
                ->where( 'rate', '=', $request->get( 'rate' ) );
        }

        if ( $statuses )
        {
            $ticketManagements
                ->whereIn( 'status_code', $statuses );
        }

        if ( !empty( $request->get( 'address_id' ) ) )
        {
            $address = Address::find( $request->get( 'address_id' ) );
        }

        if ( !empty( $exp_number[1] ) )
        {
            $ticketManagements
                ->where( 'id', '=', $exp_number[1] );
        }

        if ( $field_management && !empty( $request->get( 'management_id' ) ) )
        {
            $ticketManagements
                ->where( 'management_id', '=', $request->get( 'management_id' ) );
        }

        switch ( $request->get( 'show' ) )
        {
            case 'call':
                $ticketManagements
                    ->whereIn( 'status_code', [ 'completed_with_act', 'completed_without_act', 'not_verified' ] )
                    ->orderBy( 'id', 'asc' );
                break;
            case 'not_processed':
                $ticketManagements
                    ->whereIn( 'status_code', [ 'transferred', 'transferred_again' ] )
                    ->orderBy( 'id', 'desc' );
                break;
            case 'not_completed':
                $ticketManagements
                    ->whereIn( 'status_code', [ 'accepted', 'assigned', 'waiting' ] )
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
                    'Адрес проживания'      => $ticket->customer->getAddress(),
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
                'management'
            )
            ->paginate( 30 )
            ->appends( $request->all() );

        return view( 'tickets.index' )
            ->with( 'ticketManagements', $ticketManagements )
            ->with( 'categories', Category::orderBy( 'name' )->get() )
            ->with( 'managements', \Auth::user()->can( 'tickets.all' ) ? Management::orderBy( 'name' )->get() : Management::mine()->orderBy( 'name' )->get() )
            ->with( 'operators', User::role( 'operator' )->orderBy( 'lastname' )->get() )
            ->with( 'field_operator', $field_operator )
            ->with( 'field_management', $field_management )
            ->with( 'address', $address ?? null );

    }

    public function createDraftIfNotExists ()
    {

        $draft = Ticket
            ::where( 'author_id', '=', \Auth::user()->id )
            ->where( 'status_code', '=', 'draft' )
            ->first();

        if ( ! $draft )
        {
            $draft = new Ticket();
            $draft->status_code = 'draft';
            $draft->status_name = Ticket::$statuses[ 'draft' ];
            $draft->author_id = \Auth::user()->id;
            $draft->save();
        }

        return $draft;

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ( Request $request )
    {

        Title::add( 'Добавить заявку' );

        $res = Type
            ::orderBy( 'name' )
            ->get();

        $types = [];
        foreach ( $res as $r )
        {
            $types[ $r->category->name ][ $r->id ] = $r->name;
        }

        $draft = $this->createDraftIfNotExists();

        $regions = Region
            ::mine()
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
            ::where( 'author_id', '=', \Auth::user()->id )
            ->where( 'status_code', '=', 'draft' )
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
            if ( $ticket instanceof MessageBag )
            {
                return redirect()->back()->withErrors( $ticket );
            }
        }

        if ( !empty( $request->get( 'customer_id' ) ) )
        {
            $customer = Customer
                ::where( 'id', '=', $request->get( 'customer_id' ) )
                ->first();
            $customer->edit( $request->all() );
        }
        else
        {
            $customer = Customer::create( $request->all() );
            $ticket->customer_id = $customer->id;
            $ticket->save();
        }

        $status_code = 'no_contract';

        foreach ( $request->get( 'managements', [] ) as $manament_id )
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
            ->route( 'tickets.show', $ticket->id )
            ->with( 'success', 'Заявка успешно добавлена' );

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show ( Request $request, $id )
    {

        $status_transferred = null;
        $status_accepted = null;
        $status_completed = null;

        $ticket = Ticket::mine()->find( $id );

        if ( ! $ticket )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }

        if ( $ticket->status_code != 'cancel' && $ticket->status_code != 'no_contract' )
        {
            $status_transferred = $ticket->statusesHistory->whereIn( 'status_code', [ 'transferred', 'transferred_again' ] )->first();
            $status_accepted = $ticket->statusesHistory->where( 'status_code', 'accepted' )->first();
            $status_completed = $ticket->statusesHistory->whereIn( 'status_code', [ 'completed_with_act', 'completed_without_act' ] )->first();
        }

        Title::add( 'Заявка #' . $ticket->id . ' от ' . $ticket->created_at->format( 'd.m.Y H:i' ) );

        $dt_now = Carbon::now();

        if ( $status_transferred )
        {

            $dt_acceptance_expire = $status_transferred->created_at->addMinutes( $ticket->type->period_acceptance * 60 );
            $dt_execution_expire = $status_transferred->created_at->addMinutes( $ticket->type->period_execution * 60 );

            $dt_transferred = $status_transferred->created_at ?? null;
            $dt_accepted = $status_accepted->created_at ?? null;
            $dt_completed = $status_completed->created_at ?? null;

            if ( $dt_completed )
            {
                $execution_hours = number_format( $dt_completed->diffInMinutes( $dt_transferred ) / 60, 2, '.', '' );
            }

        }

        return view( 'tickets.show' )
            ->with( 'ticket', $ticket )
            ->with( 'ticketManagement', $ticketManagement ?? null )
            ->with( 'dt_acceptance_expire', $dt_acceptance_expire ?? null )
            ->with( 'dt_execution_expire', $dt_execution_expire ?? null )
            ->with( 'dt_transferred', $dt_transferred ?? null )
            ->with( 'dt_accepted', $dt_accepted ?? null )
            ->with( 'dt_completed', $dt_completed ?? null )
            ->with( 'dt_now', $dt_now )
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

        $status_transferred = null;
        $status_accepted = null;
        $status_completed = null;

        $ticket = Ticket
            ::mine()
            ->find( $ticket_id );

        if ( ! $ticket )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }

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
            if ( ! in_array( $ticketManagement->status_code, Ticket::$without_time ) )
            {
                $status_transferred = $ticketManagement->statusesHistory->whereIn( 'status_code', [ 'transferred', 'transferred_again' ] )->first();
                $status_accepted = $ticketManagement->statusesHistory->where( 'status_code', 'accepted' )->first();
                $status_completed = $ticketManagement->statusesHistory->whereIn( 'status_code', [ 'completed_with_act', 'completed_without_act' ] )->first();
            }
            Title::add( 'Заявка #' . $ticketManagement->getTicketNumber() . ' от ' . $ticketManagement->ticket->created_at->format( 'd.m.Y H:i' ) );
        }
        else
        {
            if ( ! in_array($ticket->status_code , Ticket::$without_time ) )
            {
                $status_transferred = $ticket->statusesHistory->whereIn( 'status_code', [ 'transferred', 'transferred_again' ] )->first();
                $status_accepted = $ticket->statusesHistory->where( 'status_code', 'accepted' )->first();
                $status_completed = $ticket->statusesHistory->whereIn( 'status_code', [ 'completed_with_act', 'completed_without_act' ] )->first();
            }
            Title::add( 'Заявка #' . $ticket->id . ' от ' . $ticket->created_at->format( 'd.m.Y H:i' ) );
        }

        $dt_now = Carbon::now();

        if ( $status_transferred )
        {

            $dt_acceptance_expire = $status_transferred->created_at->addMinutes( $ticket->type->period_acceptance * 60 );
            $dt_execution_expire = $status_transferred->created_at->addMinutes( $ticket->type->period_execution * 60 );

            $dt_transferred = $status_transferred->created_at ?? null;
            $dt_accepted = $status_accepted->created_at ?? null;
            $dt_completed = $status_completed->created_at ?? null;

            if ( $dt_completed )
            {
                $execution_hours = number_format( $dt_completed->diffInMinutes( $dt_transferred ) / 60, 2, '.', '' );
            }

        }

        return view( 'tickets.show' )
            ->with( 'ticket', $ticket )
            ->with( 'ticketManagement', $ticketManagement ?? null )
            ->with( 'dt_acceptance_expire', $dt_acceptance_expire ?? null )
            ->with( 'dt_execution_expire', $dt_execution_expire ?? null )
            ->with( 'dt_transferred', $dt_transferred ?? null )
            ->with( 'dt_accepted', $dt_accepted ?? null )
            ->with( 'dt_completed', $dt_completed ?? null )
            ->with( 'dt_now', $dt_now )
            ->with( 'execution_hours', $execution_hours ?? null );

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
		
		return redirect()
            ->route( 'tickets.show', $ticket->id )
            ->with( 'success', 'Заявка успешно отредактирована' );
		
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

    public function setExecutor ( Request $request )
    {

        $ticketManagement = TicketManagement
            ::mine()
            ->find( $request->get( 'id' ) );

        if ( ! $ticketManagement )
        {
            return redirect()
                ->route( 'tickets.index' )
                ->withErrors( [ 'Заявка не найдена' ] );
        }

        \DB::beginTransaction();

        $ticketManagement->executor = $request->get( 'executor' );
        $ticketManagement->save();

        $res = $ticketManagement->changeStatus( 'assigned', true );

        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

        $res = $ticketManagement->addLog( 'Назначен исполнитель "' . $ticketManagement->executor . '"' );

        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

        \DB::commit();

        return redirect()->back()->with( 'success', 'Исполнитель успешно назначен' );

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

    public function closed ( Request $request )
    {

        Title::add( 'Закрытые заявки' );

        if ( \Auth::user()->hasRole( 'control' ) )
        {

            return $this->closedControl();

        }
        else if ( \Auth::user()->hasRole( 'operator' ) )
        {

            return $this->closedOperator();

        }
        else if ( \Auth::user()->hasRole( 'management' ) && \Auth::user()->managements->count() )
        {

            return $this->closedManagement();

        }
        else
        {
            Title::add( 'Доступ запрещен' );
            return view( 'blank' )
                ->with( 'error', 'Доступ запрещен' );
        }

    }

    public function closedControl ()
    {

        $tickets = Ticket
            ::mine()
            ->whereIn( 'status_code', [ 'closed_with_confirm', 'closed_without_confirm' ] );

        if ( !empty( \Input::get( 'id' ) ) )
        {
            $tickets
                ->where( 'id', '=', \Input::get( 'id' ) );
        }

        if ( !empty( \Input::get( 'name' ) ) )
        {
            $tickets
                ->where( function ( $q )
                {
                    $exp = explode( ' ', \Input::get( 'name' ) );
                    foreach ( $exp as $e )
                    {
                        $s = '%' . $e . '%';
                        $q
                            ->orWhere( 'firstname', 'like', $s )
                            ->orWhere( 'middlename', 'like', $s )
                            ->orWhere( 'lastname', 'like', $s );
                    }
                    return $q;
                });
        }

        if ( !empty( \Input::get( 'phone' ) ) )
        {
            $tickets
                ->where( function ( $q )
                {
                    $s = mb_substr( preg_replace( '/[^0-9]/', '', \Input::get( 'phone' ) ), -10 );
                    return $q
                        ->where( 'phone', 'like', $s )
                        ->orWhere( 'phone2', 'like', $s );
                });
        }

        if ( !empty( \Input::get( 'type_id' ) ) )
        {
            $tickets
                ->where( 'type_id', '=', \Input::get( 'type_id' ) );
        }

        if ( !empty( \Input::get( 'address_id' ) ) )
        {
            $tickets
                ->where( 'address_id', '=', \Input::get( 'address_id' ) );
            $address = Address::find( \Input::get( 'address_id' ) );
        }

        if ( !empty( \Input::get( 'flat' ) ) )
        {
            $tickets
                ->where( 'flat', '=', \Input::get( 'flat' ) );
        }

        if ( !empty( \Input::get( 'management_id' ) ) )
        {
            $tickets
                ->whereHas( 'managements', function ( $q )
                {
                    return $q
                        ->where( 'management_id', '=', \Input::get( 'management_id' ) );
                });
        }

        $tickets = $tickets->paginate( 30 );

        return view( 'tickets.control.other' )
            ->with( 'tickets', $tickets )
            ->with( 'types', Type::orderBy( 'name' )->get() )
            ->with( 'managements', Management::orderBy( 'name' )->get() )
            ->with( 'address', $address ?? null );

    }

    public function closedOperator ()
    {

        $tickets = Ticket
            ::whereIn( 'status_code', [ 'closed_with_confirm', 'closed_without_confirm' ] );

        if ( !empty( \Input::get( 'id' ) ) )
        {
            $tickets
                ->where( 'id', '=', \Input::get( 'id' ) );
        }

        if ( !empty( \Input::get( 'name' ) ) )
        {
            $tickets
                ->where( function ( $q )
                {
                    $exp = explode( ' ', \Input::get( 'name' ) );
                    foreach ( $exp as $e )
                    {
                        $s = '%' . $e . '%';
                        $q
                            ->orWhere( 'firstname', 'like', $s )
                            ->orWhere( 'middlename', 'like', $s )
                            ->orWhere( 'lastname', 'like', $s );
                    }
                    return $q;
                });
        }

        if ( !empty( \Input::get( 'phone' ) ) )
        {
            $tickets
                ->where( function ( $q )
                {
                    $s = mb_substr( preg_replace( '/[^0-9]/', '', \Input::get( 'phone' ) ), -10 );
                    return $q
                        ->where( 'phone', 'like', $s )
                        ->orWhere( 'phone2', 'like', $s );
                });
        }

        if ( !empty( \Input::get( 'type_id' ) ) )
        {
            $tickets
                ->where( 'type_id', '=', \Input::get( 'type_id' ) );
        }

        if ( !empty( \Input::get( 'address_id' ) ) )
        {
            $tickets
                ->where( 'address_id', '=', \Input::get( 'address_id' ) );
            $address = Address::find( \Input::get( 'address_id' ) );
        }

        if ( !empty( \Input::get( 'flat' ) ) )
        {
            $tickets
                ->where( 'flat', '=', \Input::get( 'flat' ) );
        }

        if ( !empty( \Input::get( 'management_id' ) ) )
        {
            $tickets
                ->whereHas( 'managements', function ( $q )
                {
                    return $q
                        ->where( 'management_id', '=', \Input::get( 'management_id' ) );
                });
        }

        $tickets = $tickets->paginate( 30 );

        return view( 'tickets.other' )
            ->with( 'tickets', $tickets )
            ->with( 'types', Type::orderBy( 'name' )->get() )
            ->with( 'managements', Management::orderBy( 'name' )->get() )
            ->with( 'address', $address ?? null );

    }

    public function closedManagement ()
    {
		
		$ticketManagements = TicketManagement
            ::whereIn( 'management_id', \Auth::user()->managements->pluck( 'id' ) )
            ->mine()
            ->whereIn( 'status_code', [ 'closed_with_confirm', 'closed_without_confirm' ] )
            ->orderBy( 'id', 'desc' )
            ->whereHas( 'ticket', function ( $q )
            {

                if ( !empty( \Input::get( 'id' ) ) )
                {
                    $q
                        ->where( 'id', '=', \Input::get( 'id' ) );
                }

                if ( !empty( \Input::get( 'name' ) ) )
                {
                    $q
                        ->where( function ( $q2 )
                        {
                            $exp = explode( ' ', \Input::get( 'name' ) );
                            foreach ( $exp as $e )
                            {
                                $s = '%' . $e . '%';
                                $q2
                                    ->orWhere( 'firstname', 'like', $s )
                                    ->orWhere( 'middlename', 'like', $s )
                                    ->orWhere( 'lastname', 'like', $s );
                            }
                            return $q2;
                        });
                }

                if ( !empty( \Input::get( 'phone' ) ) )
                {
                    $q
                        ->where( function ( $q2 )
                        {
                            $s = mb_substr( preg_replace( '/[^0-9]/', '', \Input::get( 'phone' ) ), -10 );
                            return $q2
                                ->where( 'phone', 'like', $s )
                                ->orWhere( 'phone2', 'like', $s );
                        });
                }

                if ( !empty( \Input::get( 'type_id' ) ) )
                {
                    $q
                        ->where( 'type_id', '=', \Input::get( 'type_id' ) );
                }

                if ( !empty( \Input::get( 'address_id' ) ) )
                {
                    $q
                        ->where( 'address_id', '=', \Input::get( 'address_id' ) );
                }

                if ( !empty( \Input::get( 'flat' ) ) )
                {
                    $q
                        ->where( 'flat', '=', \Input::get( 'flat' ) );
                }

                return $q;

            });


        if ( !empty( \Input::get( 'address_id' ) ) )
        {
            $address = Address::find( \Input::get( 'address_id' ) );
        }

        $ticketManagements = $ticketManagements->paginate( 30 );

        return view( 'tickets.management.other' )
            ->with( 'ticketManagements', $ticketManagements )
            ->with( 'types', Type::orderBy( 'name' )->get() )
            ->with( 'managements', Management::orderBy( 'name' )->get() )
            ->with( 'address', $address ?? null );

    }

    public function no_contract ( Request $request )
    {

        Title::add( 'Отсутствует договор' );

        $tickets = Ticket
            ::where( 'status_code', '=', 'no_contract' );

        if ( !empty( \Input::get( 'id' ) ) )
        {
            $tickets
                ->where( 'id', '=', \Input::get( 'id' ) );
        }

        if ( !empty( \Input::get( 'name' ) ) )
        {
            $tickets
                ->where( function ( $q )
                {
                    $exp = explode( ' ', \Input::get( 'name' ) );
                    foreach ( $exp as $e )
                    {
                        $s = '%' . $e . '%';
                        $q
                            ->orWhere( 'firstname', 'like', $s )
                            ->orWhere( 'middlename', 'like', $s )
                            ->orWhere( 'lastname', 'like', $s );
                    }
                    return $q;
                });
        }

        if ( !empty( \Input::get( 'phone' ) ) )
        {
            $tickets
                ->where( function ( $q )
                {
                    $s = mb_substr( preg_replace( '/[^0-9]/', '', \Input::get( 'phone' ) ), -10 );
                    return $q
                        ->where( 'phone', 'like', $s )
                        ->orWhere( 'phone2', 'like', $s );
                });
        }

        if ( !empty( \Input::get( 'type_id' ) ) )
        {
            $tickets
                ->where( 'type_id', '=', \Input::get( 'type_id' ) );
        }

        if ( !empty( \Input::get( 'address_id' ) ) )
        {
            $tickets
                ->where( 'address_id', '=', \Input::get( 'address_id' ) );
            $address = Address::find( \Input::get( 'address_id' ) );
        }

        if ( !empty( \Input::get( 'flat' ) ) )
        {
            $tickets
                ->where( 'flat', '=', \Input::get( 'flat' ) );
        }

        if ( !empty( \Input::get( 'management_id' ) ) )
        {
            $tickets
                ->whereHas( 'managements', function ( $q )
                {
                    return $q
                        ->where( 'management_id', '=', \Input::get( 'management_id' ) );
                });
        }

        $tickets = $tickets->paginate( 30 );

        return view( 'tickets.other' )
            ->with( 'tickets', $tickets )
            ->with( 'types', Type::orderBy( 'name' )->get() )
            ->with( 'managements', Management::orderBy( 'name' )->get() )
            ->with( 'address', $address ?? null );

    }

    public function canceled ( Request $request )
    {

        Title::add( 'Отмененные заявки' );

        $tickets = Ticket
            ::where( 'status_code', '=', 'cancel' );

        if ( !empty( \Input::get( 'id' ) ) )
        {
            $tickets
                ->where( 'id', '=', \Input::get( 'id' ) );
        }

        if ( !empty( \Input::get( 'name' ) ) )
        {
            $tickets
                ->where( function ( $q )
                {
                    $exp = explode( ' ', \Input::get( 'name' ) );
                    foreach ( $exp as $e )
                    {
                        $s = '%' . $e . '%';
                        $q
                            ->orWhere( 'firstname', 'like', $s )
                            ->orWhere( 'middlename', 'like', $s )
                            ->orWhere( 'lastname', 'like', $s );
                    }
                    return $q;
                });
        }

        if ( !empty( \Input::get( 'phone' ) ) )
        {
            $tickets
                ->where( function ( $q )
                {
                    $s = mb_substr( preg_replace( '/[^0-9]/', '', \Input::get( 'phone' ) ), -10 );
                    return $q
                        ->where( 'phone', 'like', $s )
                        ->orWhere( 'phone2', 'like', $s );
                });
        }

        if ( !empty( \Input::get( 'type_id' ) ) )
        {
            $tickets
                ->where( 'type_id', '=', \Input::get( 'type_id' ) );
        }

        if ( !empty( \Input::get( 'address_id' ) ) )
        {
            $tickets
                ->where( 'address_id', '=', \Input::get( 'address_id' ) );
            $address = Address::find( \Input::get( 'address_id' ) );
        }

        if ( !empty( \Input::get( 'flat' ) ) )
        {
            $tickets
                ->where( 'flat', '=', \Input::get( 'flat' ) );
        }

        if ( !empty( \Input::get( 'management_id' ) ) )
        {
            $tickets
                ->whereHas( 'managements', function ( $q )
                {
                    return $q
                        ->where( 'management_id', '=', \Input::get( 'management_id' ) );
                });
        }

        $tickets = $tickets->paginate( 30 );

        return view( 'tickets.other' )
            ->with( 'tickets', $tickets )
            ->with( 'types', Type::orderBy( 'name' )->get() )
            ->with( 'managements', Management::orderBy( 'name' )->get() )
            ->with( 'address', $address ?? null );

    }

    public function customerTickets ( Request $request, $customer_id )
    {

        if ( ! \Auth::user()->can( 'tickets.customer_tickets' ) )
        {
            return redirect()->back()->withErrors( [ 'Доступ запрещен' ] );
        }

        Title::add( 'Заявки заявителя' );

        $tickets = Ticket
            ::where( 'customer_id', '=', $customer_id )
            ->where( 'status_code', '!=', 'draft' )
            ->orderBy( 'id', 'desc' );

        if ( !empty( \Input::get( 'search' ) ) )
        {
            $tickets
                ->fastSearch( \Input::get( 'search' ) );
        }

        if ( !empty( \Input::get( 'group' ) ) )
        {
            $tickets
                ->where( 'group_uuid', '=', \Input::get( 'group' ) );
        }

        if ( !empty( \Input::get( 'id' ) ) )
        {
            $tickets
                ->where( 'id', '=', \Input::get( 'id' ) );
        }

        if ( !empty( \Input::get( 'status_code' ) ) )
        {
            $tickets
                ->where( 'status_code', '=', \Input::get( 'status_code' ) );
        }

        if ( !empty( \Input::get( 'period_from' ) ) )
        {
            $tickets
                ->whereRaw( 'DATE( created_at ) >= ?', [ Carbon::parse( \Input::get( 'period_from' ) )->toDateTimeString() ] );
        }

        if ( !empty( \Input::get( 'period_to' ) ) )
        {
            $tickets
                ->whereRaw( 'DATE( created_at ) <= ?', [ Carbon::parse( \Input::get( 'period_to' ) )->toDateTimeString() ] );
        }

        if ( !empty( \Input::get( 'operator_id' ) ) )
        {
            $tickets
                ->where( 'author_id', '=', \Input::get( 'operator_id' ) );
        }

        if ( !empty( \Input::get( 'type_id' ) ) )
        {
            $tickets
                ->where( 'type_id', '=', \Input::get( 'type_id' ) );
        }

        if ( !empty( \Input::get( 'address_id' ) ) )
        {
            $tickets
                ->where( 'address_id', '=', \Input::get( 'address_id' ) );
            $address = Address::find( \Input::get( 'address_id' ) );
        }

        if ( !empty( \Input::get( 'flat' ) ) )
        {
            $tickets
                ->where( 'flat', '=', \Input::get( 'flat' ) );
        }

        if ( !empty( \Input::get( 'management_id' ) ) )
        {
            $tickets
                ->whereHas( 'managements', function ( $q )
                {
                    return $q
                        ->where( 'management_id', '=', \Input::get( 'management_id' ) );
                });
        }

        if ( \Input::get( 'export' ) == 1 && \Auth::user()->can( 'tickets.export' ) )
        {
            $tickets = $tickets->get();
            $data = [];
            foreach ( $tickets as $ticket )
            {
                if ( $ticket->managements->count() )
                {
                    foreach ( $ticket->managements as $ticketManagement )
                    {
                        $data[] = [
                            '#'                     => $ticket->id,
                            'Дата и время'          => $ticket->created_at->format( 'd.m.y H:i' ),
                            'Текущий статус'        => $ticket->status_name,
                            'Адрес проблемы'        => $ticket->address->name,
                            'Квартира'              => $ticket->flat,
                            'Проблемное место'      => $ticket->getPlace(),
                            'Категория заявки'      => $ticket->type->category->name,
                            'Тип заявки'            => $ticket->type->name,
                            'ФИО заявителя'         => $ticket->getName(),
                            'Телефон(ы) заявителя'  => $ticket->getPhones(),
                            'Адрес проживания'      => $ticket->customer->getAddress(),
                            'Оператор'              => $ticket->author->getName(),
                            'ЭО'                    => $ticketManagement->management->name,
                        ];
                    }
                }
                else
                {
                    if ( $ticket->status_code == 'draft' ) continue;
                    $data[] = [
                        '#'                     => $ticket->id,
                        'Дата и время'          => $ticket->created_at->format( 'd.m.y H:i' ),
                        'Текущий статус'        => $ticket->status_name,
                        'Адрес проблемы'        => $ticket->address->name,
                        'Квартира'              => $ticket->flat,
                        'Проблемное место'      => $ticket->getPlace(),
                        'Категория заявки'      => $ticket->type->category->name,
                        'Тип заявки'            => $ticket->type->name,
                        'ФИО заявителя'         => $ticket->getName(),
                        'Телефон(ы) заявителя'  => $ticket->getPhones(),
                        'Адрес проживания'      => $ticket->customer->getAddress(),
                        'Оператор'              => $ticket->author->getName(),
                        'ЭО'                    => '',
                    ];
                }
            }
            \Excel::create( 'ЗАЯВКИ', function ( $excel ) use ( $data )
            {
                $excel->sheet( 'ЗАЯВКИ', function ( $sheet ) use ( $data )
                {
                    $sheet->fromArray( $data );
                });
            })->export( 'xls' );
        }

        $tickets = $tickets->paginate( 30 );

        return view( 'tickets.customer_tickets' )
            ->with( 'tickets', $tickets )
            ->with( 'types', Type::all() )
            ->with( 'managements', Management::all() )
            ->with( 'operators', User::role( 'operator' )->get() )
            ->with( 'address', $address ?? null );

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

        return view( 'tickets.act' )
            ->with( 'ticketManagement', $ticketManagement );

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
            ->with( 'ticketManagement', $ticketManagement );
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
        if ( $ticketManagement->status_code != 'closed_with_confirm' )
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
        $ticket->edit([
            $request->get( 'field' ) => $request->get( 'value' )
        ]);
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

        $customer = Customer::find( $request->get( 'customer_id' ) );

        $tickets = Ticket
            ::where( 'customer_id', '=', $customer->id )
            ->where( 'status_code', '!=', 'draft' )
            ->orderBy( 'id', 'desc' )
            ->take( 10 )
            ->get();

        if ( $tickets->count() )
        {
            return view( 'tickets.select' )
                ->with( 'tickets', $tickets )
                ->with( 'customer', $customer );
        }

    }
	
}
