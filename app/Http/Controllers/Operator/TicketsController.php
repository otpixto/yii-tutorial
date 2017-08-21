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

        $types = Type::all();
        $managements = Management::all();
        $operators = User::role( 'operator' )->get();

        $tickets = Ticket
            ::mine()
            ->parentsOnly()
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

        $tickets = $tickets->paginate( 30 );

        return view( 'tickets.operator.index' )
            ->with( 'tickets', $tickets )
            ->with( 'types', $types )
            ->with( 'managements', $managements )
            ->with( 'operators', $operators )
            ->with( 'address', $address ?? null );

    }

    public function management ()
    {

        $types = Type::all();

        $ticketManagements = \Auth::user()->management->tickets()->mine()->orderBy( 'id', 'desc' );

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

        $ticketManagements = $ticketManagements->paginate( 30 );

        if ( !empty( \Input::get( 'address_id' ) ) )
        {
            $address = Address::find( \Input::get( 'address_id' ) );
        }

        return view( 'tickets.management.index' )
            ->with( 'ticketManagements', $ticketManagements )
            ->with( 'types', $types )
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

        return view( 'tickets.create' )
            ->with( 'types', $types )
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

        if ( ! isset( Ticket::$places[ $request->get( 'place' ) ] ) )
        {
            return redirect()->back()->withErrors( [ 'Некорректное проблемное место' ] );
        }

		\DB::beginTransaction();

        $ticket = Ticket::create( $request->all() );

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

			if ( $request->get( 'draft' ) == 1 )
			{
				$status_code = 'draft';
			}
			else
			{
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
            ::whereIn( 'status_code', [ 'completed_with_act', 'completed_without_act' ] )
            ->paginate( 30 );

        return view( 'tickets.operator.call' )
            ->with( 'tickets', $tickets );

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

        if ( $ticket->status_code != 'closed_with_confirm' )
        {
            $res = $ticket->changeStatus( 'closed_with_confirm', true );
            if ( $res instanceof MessageBag )
            {
                return redirect()->back()->withErrors( $res );
            }
        }

        $ticket->rate = $request->get( 'rate' );
        $ticket->rate_comment = $request->get( 'comment', null );
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

        \DB::commit();

        return redirect()->back()->with( 'success', 'Обращение повторно передано в ЭО' );

    }
	
}
