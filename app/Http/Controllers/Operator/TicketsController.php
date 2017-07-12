<?php

namespace App\Http\Controllers\Operator;

use App\Models\Operator\Customer;
use App\Models\Operator\Ticket;
use App\Models\Operator\Type;
use Illuminate\Http\Request;

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
}
