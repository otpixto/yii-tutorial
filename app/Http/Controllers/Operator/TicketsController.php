<?php

namespace App\Http\Controllers\Operator;

use App\Classes\Title;
use App\Models\Address;
use App\Models\Customer;
use App\Models\Management;
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
        Title::add( 'Реестр обращений' );
    }

    public function index ()
    {

        if ( \Auth::user()->hasRole( 'operator' ) )
        {

            return $this->operator();

        }
        else if ( \Auth::user()->hasRole( 'management' ) && \Auth::user()->management )
        {

            return $this->management();

        }
        else
        {
            Title::add( 'Доступ запрещен' );
            return view( 'blank' )
                ->with( 'error', 'Доступ запрещен' );
        }

    }

    public function operator ()
    {

        $operators = User::role( 'operator' )->get();

        $tickets = Ticket
            ::mine()
            ->parentsOnly()
            ->whereNotIn( 'status_code', [ 'closed_with_confirm', 'closed_without_confirm', 'cancel', 'no_contract', 'not_verified' ] )
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

        if ( \Input::get( 'export' ) == 1 )
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
                            'Проблемное место'      => $ticket->place,
                            'Категория обращения'   => $ticket->type->category->name,
                            'Тип обращения'         => $ticket->type->name,
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
                        'Проблемное место'      => $ticket->place,
                        'Категория обращения'   => $ticket->type->category->name,
                        'Тип обращения'         => $ticket->type->name,
                        'ФИО заявителя'         => $ticket->getName(),
                        'Телефон(ы) заявителя'  => $ticket->getPhones(),
                        'Адрес проживания'      => $ticket->customer->getAddress(),
                        'Оператор'              => $ticket->author->getName(),
                        'ЭО'                    => '',
                    ];
                }
            }
            \Excel::create( 'ОБРАЩЕНИЯ', function ( $excel ) use ( $data )
            {
                $excel->sheet( 'ОБРАЩЕНИЯ', function ( $sheet ) use ( $data )
                {
                    $sheet->fromArray( $data );
                });
            })->export( 'xls' );
        }

        $tickets = $tickets->paginate( 30 );

        return view( 'tickets.operator.index' )
            ->with( 'tickets', $tickets )
            ->with( 'types', Type::all() )
            ->with( 'managements', Management::all() )
            ->with( 'operators', $operators )
            ->with( 'address', $address ?? null );

    }

    public function management ()
    {

        $ticketManagements = \Auth::user()
            ->management
            ->tickets()
            ->mine()
            ->whereNotIn( 'status_code', [ 'closed_with_confirm', 'closed_without_confirm', 'cancel', 'no_contract' ] )
            ->orderBy( 'id', 'desc' );

        $ticketManagements
            ->whereHas( 'ticket', function ( $q )
            {

                if ( !empty( \Input::get( 'show' ) ) )
                {
                    switch ( \Input::get( 'show' ) )
                    {

                        case 'not_processed':

                            $q->whereIn( 'status_code', [ 'transferred', 'transferred_again' ] );

                            break;

                        case 'not_completed':

                            $q->whereIn( 'status_code', [ 'accepted', 'assigned', 'waiting' ] );

                            break;

                    }
                }

                if ( !empty( \Input::get( 'search' ) ) )
                {
                    $q
                        ->fastSearch( \Input::get( 'search' ) );
                }

                if ( !empty( \Input::get( 'id' ) ) )
                {
                    $q
                        ->where( 'id', '=', \Input::get( 'id' ) );
                }

                if ( !empty( \Input::get( 'period_from' ) ) )
                {
                    $q
                        ->where( 'created_at', '>=', Carbon::parse( \Input::get( 'period_from' ) )->setTime( 0, 0, 0 )->toDateTimeString() );
                }

                if ( !empty( \Input::get( 'period_to' ) ) )
                {
                    $q
                        ->where( 'created_at', '<=', Carbon::parse( \Input::get( 'period_to' ) )->setTime( 23, 59, 59 )->toDateTimeString() );
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

        if ( !empty( \Input::get( 'status_code' ) ) )
        {
            $ticketManagements
                ->where( 'status_code', '=', \Input::get( 'status_code' ) );
        }

        if ( \Input::get( 'export' ) == 1 )
        {
            $ticketManagements = $ticketManagements->get();
            $data = [];
            foreach ( $ticketManagements as $ticketManagement )
            {
                $ticket = $ticketManagement->ticket;
                $data[] = [
                    '#'                     => $ticket->id,
                    'Дата и время'          => $ticket->created_at->format( 'd.m.y H:i' ),
                    'Текущий статус'        => $ticketManagement->status_name,
                    'Адрес проблемы'        => $ticket->address->name,
                    'Квартира'              => $ticket->flat,
                    'Проблемное место'      => $ticket->place,
                    'Категория обращения'   => $ticket->type->category->name,
                    'Тип обращения'         => $ticket->type->name,
                    'ФИО заявителя'         => $ticket->getName(),
                    'Телефон(ы) заявителя'  => $ticket->getPhones(),
                    'Адрес проживания'      => $ticket->customer->getAddress(),
                ];
            }
            \Excel::create( 'ОБРАЩЕНИЯ', function ( $excel ) use ( $data )
            {
                $excel->sheet( 'ОБРАЩЕНИЯ', function ( $sheet ) use ( $data )
                {
                    $sheet->fromArray( $data );
                });
            })->export( 'xls' );
        }

        $ticketManagements = $ticketManagements->paginate( 30 );

        if ( !empty( \Input::get( 'address_id' ) ) )
        {
            $address = Address::find( \Input::get( 'address_id' ) );
        }

        return view( 'tickets.management.index' )
            ->with( 'ticketManagements', $ticketManagements )
            ->with( 'types', Type::all() )
            ->with( 'address', $address ?? null );

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {

        Title::add( 'Добавить обращение' );

        $res = Type
            ::orderBy( 'name' )
            ->get();

        $types = [];
        foreach ( $res as $r )
        {
            $types[ $r->category->name ][ $r->id ] = $r->name;
        }

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
            if ( \Input::get( 'phone' ) )
            {
                $draft->phone = \Input::get( 'phone' );
            }
            $draft->save();
        }

        return view( 'tickets.create' )
            ->with( 'types', $types )
            ->with( 'draft', $draft )
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
            $ticket->edit( $request->all() );
        }
        else
        {
            $ticket = Ticket::create( $request->all() );
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

            if ( $ticketManagement->management->has_contract )
            {
                $status_code = 'created';
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

        return redirect()->route( 'tickets.show', $ticket->id )
            ->with( 'success', 'Обращение успешно добавлено' );

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show ( $id )
    {

        $status_transferred = null;
        $status_accepted = null;
        $status_completed = null;

        if ( \Auth::user()->hasRole( 'operator' ) )
        {

            $view = 'tickets.operator.show';

            $ticket = Ticket::find( $id );

            if ( ! $ticket )
            {
                return redirect()->route( 'tickets.index' )
                    ->withErrors( [ 'Обращение не найдено' ] );
            }
			
			if ( $ticket->status_code == 'draft' )
			{
				return redirect()->route( 'tickets.create' );
			}

            if ( !$ticket )
            {
                return redirect()->route( 'tickets.index' )
                    ->withErrors( [ 'Обращение не найдено' ] );
            }

            if ( $ticket->status_code != 'cancel' && $ticket->status_code != 'no_contract' )
            {
                $status_transferred = $ticket->statusesHistory->whereIn( 'status_code', [ 'transferred', 'transferred_again' ] )->first();
                $status_accepted = $ticket->statusesHistory->where( 'status_code', 'accepted' )->first();
                $status_completed = $ticket->statusesHistory->whereIn( 'status_code', [ 'completed_with_act', 'completed_without_act' ] )->first();
            }

        }
        else if ( \Auth::user()->hasRole( 'management' ) && \Auth::user()->management )
        {

            $view = 'tickets.management.show';

            $ticketManagement = TicketManagement
                ::where( 'ticket_id', '=', $id )
                ->where( 'management_id', '=', \Auth::user()->management->id )
                ->mine()
                ->first();

            if ( !$ticketManagement )
            {
                return redirect()->route( 'tickets.index' )
                    ->withErrors( [ 'Обращение не найдено' ] );
            }

            $ticket = $ticketManagement->ticket;

            if ( !$ticket )
            {
                return redirect()->route( 'tickets.index' )
                    ->withErrors( [ 'Обращение не найдено' ] );
            }

            if ( $ticketManagement->status_code != 'cancel' && $ticketManagement->status_code != 'no_contract' )
            {
                $status_transferred = $ticketManagement->statusesHistory->whereIn( 'status_code', [ 'transferred', 'transferred_again' ] )->first();
                $status_accepted = $ticketManagement->statusesHistory->where( 'status_code', 'accepted' )->first();
                $status_completed = $ticketManagement->statusesHistory->whereIn( 'status_code', [ 'completed_with_act', 'completed_without_act' ] )->first();
            }

        }
        else
        {
            Title::add( 'Доступ запрещен' );
            return view( 'blank' )
                ->with( 'error', 'Доступ запрещен' );
        }

        Title::add( 'Обращение #' . $ticket->id . ' от ' . $ticket->created_at->format( 'd.m.Y H:i' ) );

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

        return view( $view )
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
			
				return view( 'tickets.operator.edit.type' )
					->with( 'ticket', $ticket )
					->with( 'types', $types )
					->with( 'param', $param );
			
				break;
				
			case 'address':
			
				return view( 'tickets.operator.edit.address' )
					->with( 'ticket', $ticket )
					->with( 'param', $param );
			
				break;
				
			case 'mark':
			
				return view( 'tickets.operator.edit.mark' )
					->with( 'ticket', $ticket )
					->with( 'param', $param );
			
				break;
				
			case 'text':
			
				return view( 'tickets.operator.edit.text' )
					->with( 'ticket', $ticket )
					->with( 'param', $param );
			
				break;
				
			case 'name':
			
				return view( 'tickets.operator.edit.name' )
					->with( 'ticket', $ticket )
					->with( 'param', $param );
			
				break;
				
			case 'phone':
			
				return view( 'tickets.operator.edit.phone' )
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
			return redirect()->route( 'tickets.index' )
				->withErrors( [ 'Обращение не найдено' ] );
		}
		
		$ticket->edit( $request->all() );
		
		return redirect()->route( 'tickets.show', $ticket->id )
            ->with( 'success', 'Обращение успешно отредактировано' );
		
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

    public function changeStatus ( Request $request, $id )
    {

        $ticket = Ticket::find( $id );

        if ( !$ticket )
        {
            return redirect()->route( 'tickets.index' )
                ->withErrors( [ 'Обращение не найдено' ] );
        }

        $res = $ticket->changeStatus( $request->get( 'status_code' ) );

        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

        return redirect()->back()->with( 'success', 'Статус изменен' );

    }

    public function changeManagementStatus ( Request $request, $id )
    {

        $ticketManagement = TicketManagement::find( $id );
        if ( !$ticketManagement )
        {
            return redirect()->back()
                ->withErrors( [ 'Исполнитель по данному обращнию не найден' ] );
        }

        $res = $ticketManagement->changeStatus( $request->get( 'status_code' ) );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

        return redirect()->back()->with( 'success', 'Статус изменен' );

    }

    public function setExecutor ( Request $request, $id )
    {

        $ticketManagement = TicketManagement::find( $id );

        if ( ! $ticketManagement )
        {
            return redirect()->route( 'tickets.index' )
                ->withErrors( [ 'Обращение не найдено' ] );
        }

        $ticketManagement->executor = $request->get( 'executor' );
        $ticketManagement->save();

        $res = $ticketManagement->changeStatus( 'assigned', true );

        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

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
                    return redirect()->back()->withErrors( [ 'Для группировки необходимо выбрать 2 или более обращения' ] );
                }

                $tickets = Ticket
                    ::whereIn( 'id', $request->get( 'tickets' ) )
                    //->orderBy( 'id', 'desc' )
                    ->get();

                if ( $tickets->count() != count( $request->get( 'tickets' ) ) )
                {
                    return redirect()->back()->withErrors( [ 'Количество выбранных обращений не совпадает с количество найденных!' ] );
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
                    return redirect()->back()->withErrors( [ 'Выберите хотя бы одно обращение' ] );
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
                    return redirect()->back()->withErrors( [ 'Выберите хотя бы одно обращение' ] );
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

    public function call ( Request $request )
    {

        Title::add( 'Обзвон' );

        $tickets = Ticket
            ::whereIn( 'status_code', [ 'completed_with_act', 'completed_without_act', 'not_verified' ] )
            ->paginate( 30 );

        return view( 'tickets.operator.call' )
            ->with( 'tickets', $tickets );

    }

    public function closed ( Request $request )
    {

        Title::add( 'Закрытые обращения' );

        if ( \Auth::user()->hasRole( 'operator' ) )
        {

            return $this->closedOperator();

        }
        else if ( \Auth::user()->hasRole( 'management' ) && \Auth::user()->management )
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

        return view( 'tickets.operator.other' )
            ->with( 'tickets', $tickets )
            ->with( 'types', Type::orderBy( 'name' )->get() )
            ->with( 'managements', Management::orderBy( 'name' )->get() )
            ->with( 'address', $address ?? null );

    }

    public function closedManagement ()
    {

        $ticketManagements = \Auth::user()
            ->management
            ->tickets()
            ->mine()
            ->whereIn( 'status_code', [ 'closed_with_confirm', 'closed_without_confirm' ] )
            ->orderBy( 'id', 'desc' );

        $ticketManagements
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

        return view( 'tickets.operator.other' )
            ->with( 'tickets', $tickets )
            ->with( 'types', Type::orderBy( 'name' )->get() )
            ->with( 'managements', Management::orderBy( 'name' )->get() )
            ->with( 'address', $address ?? null );

    }

    public function canceled ( Request $request )
    {

        Title::add( 'Отмененные обращения' );

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

        return view( 'tickets.operator.other' )
            ->with( 'tickets', $tickets )
            ->with( 'types', Type::orderBy( 'name' )->get() )
            ->with( 'managements', Management::orderBy( 'name' )->get() )
            ->with( 'address', $address ?? null );

    }

    public function act ( $id )
    {

        $ticketManagement = TicketManagement::find( $id );

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
        return view( 'tickets.operator.edit.add_management' )
            ->with( 'ticket', $ticket )
			->with( 'managements', $managements );
    }
	
	public function postAddManagement ( Request $request, $id )
    {
        $ticket = Ticket::find( $request->get( 'id' ) );
		if ( ! $ticket )
        {
            return redirect()->route( 'tickets.index' )
                ->withErrors( [ 'Обращение не найдено' ] );
        }
		$management_id = $request->get( 'management_id' );
		if ( ! $management_id )
		{
			return redirect()->route( 'tickets.show', $ticket->id )
                ->withErrors( [ 'ЭО не выбрана' ] );
		}
        $ticketManagement = TicketManagement::create([
			'ticket_id'         => $ticket->id,
			'management_id'     => $request->get( 'management_id' ),
		]);
		return redirect()->route( 'tickets.show', $ticket->id )
            ->with( 'success', 'ЭО успешно добавлена' );
    }
	
	public function postDelManagement ( Request $request )
    {
        $ticketManagement = TicketManagement::find( $request->get( 'id' ) );
		$ticketManagement->delete();
    }

    public function getRateForm ( Request $request )
    {
        $ticket = Ticket::find( $request->get( 'id' ) );
        return view( 'parts.rate_form' )
            ->with( 'ticket', $ticket );
    }

    public function postRateForm ( Request $request )
    {

        $ticket = Ticket::find( $request->get( 'id' ) );

        if ( !$ticket )
        {
            return redirect()->route( 'tickets.index' )
                ->withErrors( [ 'Обращение не найдено' ] );
        }

        \DB::beginTransaction();

        $ticket->rate = $request->get( 'rate' );
        $ticket->rate_comment = $request->get( 'comment', null );

        if ( $ticket->status_code != 'closed_with_confirm' )
        {
            $res = $ticket->changeStatus( 'closed_with_confirm', true );
            if ( $res instanceof MessageBag )
            {
                return redirect()->back()->withErrors( $res );
            }
        }

        $ticket->save();

        /*$group = $ticket->group()->where( 'id', '!=', $ticket->id )->get();

        if ( $group->count() )
        {
            foreach ( $group as $row )
            {
                $row->rate = $ticket->rate;
                $row->rate_comment = $ticket->rate_comment;
                $row->save();
            }
        }*/

        \DB::commit();

        return redirect()->back()->with( 'success', 'Ваша оценка учтена' );

    }

    public function postClose ( Request $request )
    {

        $ticket = Ticket::find( $request->get( 'id' ) );

        if ( !$ticket )
        {
            return redirect()->route( 'tickets.index' )
                ->withErrors( [ 'Обращение не найдено' ] );
        }

        \DB::beginTransaction();

        if ( $ticket->status_code != 'closed_without_confirm' )
        {
            $res = $ticket->changeStatus( 'closed_without_confirm', true );
            if ( $res instanceof MessageBag )
            {
                return redirect()->back()->withErrors( $res );
            }
        }

        \DB::commit();

        return redirect()->back()->with( 'success', 'Обращение успешно закрыто' );

    }

    public function postRepeat ( Request $request )
    {

        $ticket = Ticket::find( $request->get( 'id' ) );

        if ( !$ticket )
        {
            return redirect()->route( 'tickets.index' )
                ->withErrors( [ 'Обращение не найдено' ] );
        }

        \DB::beginTransaction();

        if ( $ticket->status_code != 'transferred_again' )
        {
            $res = $ticket->changeStatus( 'transferred_again', true );
            if ( $res instanceof MessageBag )
            {
                return redirect()->back()->withErrors( $res );
            }
        }

        $text = trim( $request->get( 'comment', '' ) );

        if ( !empty( $text ) )
        {

            $comment = $ticket->addComment( $request->get( 'comment' ) );

            $author = $comment->author->getName();

            if ( $comment->author->hasRole( 'operator' ) )
            {
                $author = '<i>[Оператор ЕДС]</i> ' . $author;
            }
            elseif ( $comment->author->hasRole( 'management' ) && $comment->author->management )
            {
                $author = '<i>[' . $comment->author->management->name . ']</i> ' . $author;
            }

            $message = '<em>Добавлен комментарий</em>' . PHP_EOL . PHP_EOL;

            $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
            $message .= 'Автор комментария: ' . $author . PHP_EOL;

            $message .= PHP_EOL . $comment->text . PHP_EOL;

            $message .= PHP_EOL . route( 'tickets.show', $ticket->id ) . PHP_EOL;

            $ticket->sendTelegram( $message );

        }

        \DB::commit();

        return redirect()->back()->with( 'success', 'Обращение повторно передано в ЭО' );

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
        if ( !$ticket )
        {
            return redirect()->route( 'tickets.index' )
                ->withErrors( [ 'Обращение не найдено' ] );
        }
        if ( $ticket->status_code != 'draft' )
        {
            return redirect()->route( 'tickets.index' )
                ->withErrors( [ 'Невозможно отменить добавленное обращение' ] );
        }
        $ticket->delete();
        return redirect()->route( 'tickets.index' );
    }
	
}
