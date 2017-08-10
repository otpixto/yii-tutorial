<?php

namespace App\Http\Middleware;

use App\Models\Ticket;
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
