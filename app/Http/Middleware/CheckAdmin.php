<?php

namespace App\Http\Middleware;

use Closure;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle ( $request, Closure $next )
    {
		if ( \Auth::user() && \Auth::user()->admin )
		{
			config([ 'debug' => true ]);
		}
		else
		{
			\Debugbar::disable();
		}
        return $next( $request );
    }
}
