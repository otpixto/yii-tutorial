<?php

namespace App\Http\Middleware;

use App\Models\Region;
use Closure;

class CheckRegion
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
            if ( Region::isOperatorUrl() )
            {
                if ( \Auth::user() && \Auth::user()->isActive() && ! \Auth::user()->admin && ! \Auth::user()->can( 'supervisor.all_regions' ) )
                {
                    return redirect()->route( 'error.403' );
                }
            }
            else
            {
                $region = Region::getCurrent();
                if ( ! $region )
                {
                    return response( view('errors.404' ) );
                }
                if ( \Auth::user() && \Auth::user()->isActive() && ! \Auth::user()->regions()->mine()->where( Region::$_table . '.id', $region->id )->count() && ! \Auth::user()->admin && ! \Auth::user()->can( 'supervisor.all_regions' ) )
                {
                    return redirect()->route( 'error.403' );
                }
                Region::$current_region = $region;
            }
        }
        return $next( $request );
    }
}
