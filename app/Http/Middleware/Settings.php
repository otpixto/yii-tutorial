<?php

namespace App\Http\Middleware;

use Closure;

class Settings
{
    public function handle($request, Closure $next)
    {
        if ( ! \Cache::has( 'settings' ) )
        {
            $res = \App\Models\Settings::get();
            $settings = [];
            foreach ( $res as $r )
            {
                $settings[ $r->key ] = $r->val;
            }
            \Cache::put( 'settings', $settings, 5 );
        }
        return $next( $request );
    }
}