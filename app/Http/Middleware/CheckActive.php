<?php

namespace App\Http\Middleware;

use App\Exceptions\InactiveException;
use Closure;

class CheckActive
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
        if ( \Auth::user() && ! \Auth::user()->isActive() )
        {
            throw new InactiveException;
        }
        return $next( $request );
    }
}
