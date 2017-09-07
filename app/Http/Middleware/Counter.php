<?php

namespace App\Http\Middleware;

use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Models\Work;
use Carbon\Carbon;
use Closure;

class Counter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle ( $request, Closure $next )
    {

        $user = $request->user();

        if ( $user )
        {

            $now = Carbon::now()->toDateString();

            if ( $user->hasRole( 'operator' ) )
            {
                $tickets_count = Ticket
                    ::mine()
                    ->whereNotIn( 'status_code', [ 'closed_with_confirm', 'closed_without_confirm', 'cancel', 'no_contract', 'not_verified' ] )
                    ->count();
                \Session::put( 'tickets_count', $tickets_count );
                $tickets_call_count = Ticket
                    ::whereIn( 'status_code', [ 'completed_with_act', 'completed_without_act', 'not_verified' ] )
                    ->count();
                \Session::put( 'tickets_call_count', $tickets_call_count );
                $works_count = Work
                    ::whereRaw( 'DATE( time_begin ) <= ? AND DATE( time_end ) >= ?', [ $now, $now ] )
                    ->count();
                \Session::put( 'works_count', $works_count );
            }
            else if ( $user->hasRole( 'management' ) && $user->managements->count() )
            {
                $tickets_count = TicketManagement
                    ::whereIn( 'management_id', \Auth::user()->managements->pluck( 'id' ) )
                    ->mine()
                    ->whereNotIn( 'status_code', [ 'closed_with_confirm', 'closed_without_confirm', 'cancel', 'no_contract', 'not_verified' ] )
                    ->count();
                $count_not_processed = TicketManagement
                    ::whereIn( 'management_id', \Auth::user()->managements->pluck( 'id' ) )
                    ->mine()
                    ->whereIn( 'status_code', [ 'transferred', 'transferred_again' ] )
                    ->count();
                $count_not_completed = TicketManagement
                    ::whereIn( 'management_id', \Auth::user()->managements->pluck( 'id' ) )
                    ->mine()
                    ->whereIn( 'status_code', [ 'accepted', 'assigned', 'waiting' ] )
                    ->count();
                \Session::put( 'tickets_count', $tickets_count );
                \Session::put( 'count_not_processed', $count_not_processed );
                \Session::put( 'count_not_completed', $count_not_completed );
            }

        }

        return $next( $request );

    }

}
