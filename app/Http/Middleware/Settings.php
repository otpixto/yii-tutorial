<?php

namespace App\Http\Middleware;

use Closure;

class Settings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $settings = \App\Models\Settings::first();
        \Session::put( 'settings', $settings );
        return $next($request);
    }
}
