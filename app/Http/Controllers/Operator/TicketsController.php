<?php

namespace App\Http\Controllers\Operator;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
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

        return view( 'operator.tickets.index' )
            ->with( 'tickets', $tickets );

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

        return view( 'operator.tickets.create' )
            ->with( 'types', $types );
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
                $status_code = 'accepted_operator';
            }
            else
            {
                $res = $ticketManagement->changeStatus( 'no_contract' );
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

		$res = $ticket->changeStatus( $status_code );

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

        $ticket = Ticket::find( $id );

        if ( !$ticket )
        {
            return redirect()->route( 'tickets.index' )
                ->withErrors( [ 'Обращение не найдено' ] );
        }

        return view( 'operator.tickets.show' )
            ->with( 'ticket', $ticket );

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
		
		$ticket->addComment( $request->get( 'text' ) );
		
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

        $res = $ticket->changeStatus( $request->get( 'status' ) );
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

        $res = $ticketManagement->changeStatus( $request->get( 'status' ) );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $res );
        }

        return redirect()->back()->with( 'success', 'Статус изменен' );

    }

    public function action ( Request $request )
    {

        if ( count( $request->get( 'tickets', [] ) ) != 0 )
        {

            $tickets = Ticket
                ::whereIn( 'id', $request->get( 'tickets' ) )
                ->get();

            switch ( $request->get( 'action' ) )
            {

                case 'group':
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
                    foreach ( $tickets as $ticket )
                    {
                        $ticket->group_uuid = null;
                        $ticket->parent_id = null;
                        $ticket->save();
                    }
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

        }

        return redirect()->back()->with( 'success', 'Готово' );

    }
	
}
