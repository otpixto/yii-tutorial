<?php

namespace App\Http\Controllers\Operator;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

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

        $tickets = Ticket
            ::mine();

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
                        ->orWhere( 'text', 'like', $s );
                });
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
            ::where( 'phone', '=', $ticket->phone )
            ->where( 'lastname', '=', trim( $ticket->lastname ) )
            ->where( 'middlename', '=', trim( $ticket->middlename ) )
            ->where( 'firstname', '=', trim( $ticket->firstname ) )
            ->first();

        if ( !$customer )
        {
            $this->validate( $request->all(), Customer::$rules );
            $customer = Customer::create( $request->all() );
            $ticket->customer_id = $customer->id;
            $ticket->save();
        }

        if ( !empty( $request->get( 'managements' ) ) )
        {
            $ticket->managements()->attach( $request->get( 'managements' ) );
        }

		if ( !empty( $request->get( 'tags' ) ) )
		{
			$tags = explode( ',', $request->get( 'tags' ) );
			foreach ( $tags as $tag )
			{
				$ticket->addTag( $tag );
			}
		}

		$res = $ticket->changeStatus( 'accepted_operator' );
        if ( $res instanceof MessageBag )
        {
            return redirect()->back()
                ->withInput()
                ->withErrors( $res );
        }

		\DB::commit();

        return redirect()->route( 'tickets.index' )
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
        //
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

        return redirect()->back()->with( 'success', 'Комментарий добавлен' );

    }
	
}
