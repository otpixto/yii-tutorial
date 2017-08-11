<?php

namespace App\Http\Middleware;

use App\Models\Ticket;
use App\Models\Work;
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

            if ( $user->hasRole( 'operator' ) )
            {
                $tickets_count = Ticket::mine()->count();
                \Session::put( 'tickets_count', $tickets_count );
                $tickets_call_count = Ticket::whereIn( 'status_code', [ 'completed_with_act', 'completed_without_act' ] )->count();
                \Session::put( 'tickets_call_count', $tickets_call_count );
                $works_count = Work::count();
                \Session::put( 'works_count', $works_count );
            }
            else if ( $user->hasRole( 'management' ) && $user->management )
            {
                $tickets_count = $user->management->tickets()->mine()->count();
                \Session::put( 'tickets_count', $tickets_count );
            }

        }

        return $next( $request );

    }

}
