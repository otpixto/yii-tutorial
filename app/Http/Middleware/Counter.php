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

            if ( $user->can( 'tickets.counter' ) )
            {

                $tickets_count = TicketManagement
                    ::mine()
                    ->notFinaleStatuses()
                    ->count();

                $count_not_processed = TicketManagement
                    ::mine()
                    ->whereIn( 'status_code', [ 'transferred', 'transferred_again' ] )
                    ->count();

                $count_in_progress = TicketManagement
                    ::mine()
                    ->whereIn( 'status_code', [ 'accepted', 'assigned', 'waiting' ] )
                    ->count();

                $count_completed = TicketManagement
                    ::mine()
                    ->whereIn( 'status_code', [ 'completed_with_act', 'completed_without_act', 'not_verified' ] )
                    ->count();

            }

            if ( $user->can( 'works.counter' ) )
            {
                $works_count = Work
                    ::mine()
                    ->current()
                    ->count();
            }

        }

        \Session::put( 'tickets_count', $tickets_count ?? 0 );
        \Session::put( 'count_not_processed', $count_not_processed ?? 0 );
        \Session::put( 'count_in_progress', $count_in_progress ?? 0 );
        \Session::put( 'count_completed', $count_completed ?? 0 );
        \Session::put( 'works_count', $works_count ?? 0 );

        return $next( $request );

    }

}
