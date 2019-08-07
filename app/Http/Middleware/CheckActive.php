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
            abort( 403, 'Пользователь не активирован' );
        }
        return $next( $request );
    }
}
