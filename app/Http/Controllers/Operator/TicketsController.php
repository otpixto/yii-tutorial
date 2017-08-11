<?php

namespace App\Http\Controllers\Operator;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use function PHPSTORM_META\elementType;
use Ramsey\Uuid\Uuid;

class TicketsController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $search = trim( \Input::get( 'search', '' ) );
        $group = trim( \Input::get( 'group', '' ) );

        if ( \Auth::user()->hasRole( 'operator' ) )
        {
            $view = 'tickets.operator.index';
            $tickets = Ticket
                ::mine()
                ->parentsOnly()
                ->orderBy( 'id', 'desc' );
            if ( !empty( $search ) )
            {
                $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
                $tickets
                    ->where( function ( $q ) use ( $s )
                    {
                        return $q
                            ->where( 'firstname', 'like', $s )
                            ->orWhere( 'middlename', 'like', $s )
                            ->orWhere( 'lastname', 'like', $s )
                            ->orWhere( 'phone', 'like', $s )
                            ->orWhere( 'phone2', 'like', $s )
                            ->orWhere( 'address', 'like', $s )
                            ->orWhere( 'text', 'like', $s );
                    });
            }
            if ( !empty( $group ) )
            {
                $tickets
                    ->where( 'group_uuid', '=', $group );
            }

            $tickets = $tickets->paginate( 30 );

            return view( 'tickets.operator.index' )
                ->with( 'tickets', $tickets )
                ->with( 'title', 'Реестр обращений' );

        }
        else if ( \Auth::user()->hasRole( 'management' ) && \Auth::user()->management )
        {

            $ticketManagements = \Auth::user()->management->tickets()->mine()->orderBy( 'id', 'desc' );

            if ( !empty( $search ) )
            {
                $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
                $ticketManagements
                    ->whereHas( 'ticket', function ( $q ) use ( $s )
                    {
                        return $q
                            ->where( 'firstname', 'like', $s )
                            ->orWhere( 'middlename', 'like', $s )
                            ->orWhere( 'lastname', 'like', $s )
                            ->orWhere( 'phone', 'like', $s )
                            ->orWhere( 'phone2', 'like', $s )
                            ->orWhere( 'address', 'like', $s )
                            ->orWhere( 'text', 'like', $s );
                    });
            }

            $ticketManagements = $ticketManagements->paginate( 30 );

            return view( 'tickets.management.index' )
                ->with( 'ticketManagements', $ticketManagements )
                ->with( 'title', 'Реестр обращений' );

        }
        else
        {
            return view( 'blank' )
                ->with( 'error', 'Доступ запрещен' )
                ->with( 'title', 'Доступ запрещен' );
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create ()
    {

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
            ->with( 'places', Ticket::$places )
            ->with( 'title', 'Добавить обращение' );
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

        if ( ! isset( Ticket::$places[ $request->get( 'place' ) ] ) )
        {
            return redirect()->back()->withErrors( [ 'Некорректное проблемное место' ] );
        }

		\DB::beginTransaction();

        $ticket = Ticket::create( $request->all() );

        $customer = Customer
            ::where( function ( $q ) use ( $ticket )
            {
                return $q
                    ->where( 'phone', '=', $ticket->phone )
                    ->orWhere( 'phone', '=', $ticket->phone2 )
                    ->orWhere( 'phone2', '=', $ticket->phone )
                    ->orWhere( 'phone2', '=', $ticket->phone2 );
            })
            ->where( 'lastname', '=', trim( $ticket->lastname ) )
            ->where( 'middlename', '=', trim( $ticket->middlename ) )
            ->where( 'firstname', '=', trim( $ticket->firstname ) )
            ->first();

        if ( !$customer )
        {
            $this->validate( $request, Customer::$rules );
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
            return view( 'blank' )
                ->with( 'error', 'Доступ запрещен' )
                ->with( 'title', 'Доступ запрещен' );
        }

        $title = 'Обращение #' . $ticket->id . ' от ' . $ticket->created_at->format( 'd.m.Y H:i' );

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
            ->with( 'execution_hours', $execution_hours ?? null )
            ->with( 'title', $title );

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        return redirect()->route( 'tickets.show', $id );

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
	
	public function comment ( Request $request, $id )
    {
        
		$ticket = Ticket::find( $id );

		if ( !$ticket )
		{
			return redirect()->route( 'tickets.index' )
                ->withErrors( [ 'Обращение не найдено' ] );
		}

		\DB::beginTransaction();

        $ticket->addComment( $request->get( 'text' ) );

        $group = $ticket->group()->where( 'id', '!=', $ticket->id )->get();

		if ( $group->count() )
        {
            foreach ( $group as $row )
            {
                $row->addComment( $request->get( 'text' ) );
            }
        }

        \DB::commit();

		return redirect()->back()->with( 'success', 'Комментарий добавлен' );
		
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

    public function action ( Request $request )
    {

        if ( count( $request->get( 'tickets', [] ) ) < 2 )
        {
            return redirect()->back()->withErrors( [ 'Для группировки необходимо выбрать 2 или более обращения' ] );
        }

        $tickets = Ticket
            ::whereIn( 'id', $request->get( 'tickets' ) )
            //->orderBy( 'id', 'desc' )
            ->get();

        switch ( $request->get( 'action' ) )
        {

            case 'group':
                if ( $tickets->count() != count( $request->get( 'tickets' ) ) )
                {
                    return redirect()->back()->withErrors( [ 'Количество выбранных обращений не совпадает с количество найденных!' ] );
                }
                $uuid = Uuid::uuid4()->toString();
                $parent = null;
                \DB::beginTransaction();
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
                \DB::commit();
                break;

            case 'ungroup':
                \DB::beginTransaction();
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
                \DB::commit();
                break;

            case 'delete':

                foreach ( $tickets as $ticket )
                {
                    $ticket->delete();
                }
                break;

            default:
                return redirect()->back()->withErrors( [ 'Некорректное действие' ] );
                break;

        }

        return redirect()->back()->with( 'success', 'Готово' );

    }

    public function rate ( Request $request, $id )
    {

        $ticket = Ticket::find( $id );

        if ( !$ticket )
        {
            return redirect()->route( 'tickets.index' )
                ->withErrors( [ 'Обращение не найдено' ] );
        }

        $ticket->rate = $request->get( 'rate' );
        $ticket->rate_comment = $request->get( 'comment', null );
        $ticket->save();

        $group = $ticket->group()->where( 'id', '!=', $ticket->id )->get();

        if ( $group->count() )
        {
            foreach ( $group as $row )
            {
                $row->rate = $ticket->rate;
                $row->rate_comment = $ticket->rate_comment;
                $row->save();
            }
        }

        return redirect()->back()->with( 'success', 'Ваша оценка учтена' );

    }

    public function act ( $id )
    {

        $ticketManagement = TicketManagement::find( $id );

        return view( 'tickets.act' )
            ->with( 'ticketManagement', $ticketManagement );

    }
	
}
