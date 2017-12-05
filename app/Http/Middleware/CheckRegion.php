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
            $user = \Auth::user();
            if ( $user && $user->isActive() && ! $user->can( 'supervisor.all_regions' ) )
            {
                return response( view('errors.403' ) );
            }
        }
        else
        {
            $region = Region::getCurrent();
            if ( ! $region )
            {
                return response( view('errors.404' ) );
            }
            if ( \Auth::user() && ! Region::mine()->where( 'id', $region->id )->count() )
            {
                return response( view('errors.403' ) );
            }
            Region::$current_region = $region;
        }
        return $next( $request );
    }
}
