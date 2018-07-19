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
        $cache_time = 5;

        if ( $user )
        {

            if ( $user->can( 'tickets.counter' ) )
            {

                if ( ! \Cache::tags( 'tickets_counts' )->has( 'user.' . $user->id . '.tickets_count' ) )
                {
                    $tickets_count = TicketManagement
                        ::mine()
                        ->notFinaleStatuses()
                        ->count();
                    \Cache::tags( 'tickets_counts' )->put( 'user.' . $user->id . '.tickets_count', $tickets_count, $cache_time );
                }

                if ( ! \Cache::tags( 'tickets_counts' )->has( 'user.' . $user->id . '.tickets_overdue_count' ) )
                {
                    $tickets_overdue_count = TicketManagement
                        ::mine()
                        ->whereHas( 'ticket', function ( $ticket )
                        {
                            return $ticket
                                ->whereRaw( 'COALESCE( accepted_at, CURRENT_TIMESTAMP ) > deadline_acceptance' )
                                ->orWhereRaw( 'COALESCE( completed_at, CURRENT_TIMESTAMP ) > deadline_execution' );
                        })
                        ->count();
                    \Cache::tags( 'tickets_counts' )->put( 'user.' . $user->id . '.tickets_overdue_count', $tickets_overdue_count, $cache_time );
                }

                if ( ! \Cache::tags( 'tickets_counts' )->has( 'user.' . $user->id . '.tickets_not_processed_count' ) )
                {
                    $tickets_not_processed_count = TicketManagement
                        ::mine()
                        ->whereIn( TicketManagement::$_table . '.status_code', [ 'transferred', 'transferred_again' ] )
                        ->count();
                    \Cache::tags( 'tickets_counts' )->put( 'user.' . $user->id . '.tickets_not_processed_count', $tickets_not_processed_count, 1 );
                }

                if ( ! \Cache::tags( 'tickets_counts' )->has( 'user.' . $user->id . '.tickets_in_progress_count' ) )
                {
                    $tickets_in_progress_count = TicketManagement
                        ::mine()
                        ->whereIn( TicketManagement::$_table . '.status_code', [ 'accepted', 'assigned', 'waiting' ] )
                        ->count();
                    \Cache::tags( 'tickets_counts' )->put( 'user.' . $user->id . '.tickets_in_progress_count', $tickets_in_progress_count, $cache_time );
                }

                if ( ! \Cache::tags( 'tickets_counts' )->has( 'user.' . $user->id . '.tickets_in_progress_count' ) )
                {
                    $tickets_in_progress_count = TicketManagement
                        ::mine()
                        ->whereIn( TicketManagement::$_table . '.status_code', [ 'accepted', 'assigned', 'waiting' ] )
                        ->count();
                    \Cache::tags( 'tickets_counts' )->put( 'user.' . $user->id . '.tickets_in_progress_count', $tickets_in_progress_count, $cache_time );
                }

                if ( ! \Cache::tags( 'tickets_counts' )->has( 'user.' . $user->id . '.tickets_completed_count' ) )
                {
                    $tickets_completed_count = TicketManagement
                        ::mine()
                        ->whereIn( TicketManagement::$_table . '.status_code', [ 'completed_with_act', 'completed_without_act', 'not_verified' ] )
                        ->count();
                    \Cache::tags( 'tickets_counts' )->put( 'user.' . $user->id . '.tickets_completed_count', $tickets_completed_count, $cache_time );
                }

            }

            if ( $user->can( 'works.counter' ) )
            {

                if ( ! \Cache::tags( 'works_counts' )->has( 'user.' . $user->id . '.works_count' ) )
                {
                    $works_count = Work
                        ::mine()
                        ->current()
                        ->whereRaw( 'time_end >= COALESCE( time_end_fact, CURRENT_TIMESTAMP )' )
                        ->count();
                    \Cache::tags( 'works_counts' )->put( 'user.' . $user->id . '.works_count', $works_count, $cache_time );
                }

                if ( ! \Cache::tags( 'works_counts' )->has( 'user.' . $user->id . '.works_overdue_count' ) )
                {
                    $works_overdue_count = Work
                        ::mine()
                        ->current()
                        ->whereRaw( 'time_end < COALESCE( time_end_fact, CURRENT_TIMESTAMP )' )
                        ->count();
                    \Cache::tags( 'works_counts' )->put( 'user.' . $user->id . '.works_overdue_count', $works_overdue_count, $cache_time );
                }
            }

        }

        return $next( $request );

    }

}
