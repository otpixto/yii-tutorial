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
                return response( view('errors.404' ) );
            }
            if ( \Auth::user() && \Auth::user()->isActive() && ! \Auth::user()->providers()->mine()->find( $provider->id ) && ! \Auth::user()->admin )
            {
                return redirect()->route( 'error.403' );
            }
        }
        return $next( $request );
    }
}
