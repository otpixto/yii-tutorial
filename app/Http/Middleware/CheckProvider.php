<?php

namespace App\Http\Middleware;

use App\Models\Provider;
use Closure;

class CheckProvider
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
        if ( $request->getUri() != route( 'logout' ) )
        {
            $provider = Provider::getCurrent();
            if ( ! $provider )
            {
                abort( 404 );
            }
            if ( \Auth::user() && \Auth::user()->isActive() && ! \Auth::user()->providers->find( $provider->id ) && ! \Auth::user()->admin )
            {
                abort( 403 );
            }
        }
        return $next( $request );
    }
}
