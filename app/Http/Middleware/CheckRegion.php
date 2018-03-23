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
            if ( \Auth::user() && \Auth::user()->isActive() && ! Region::mine()->where( 'id', $region->id )->count() )
            {
                return redirect()->route( 'error.403' );
            }
            Region::$current_region = $region;
        }
        return $next( $request );
    }
}
