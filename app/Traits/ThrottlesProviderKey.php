<?php

namespace App\Traits;

use App\Models\ProviderKey;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiter;
use Illuminate\Auth\Events\Lockout;

trait ThrottlesProviderKey
{

    protected function hasTooManyProviderKeyAttempts ( Request $request, ProviderKey $providerKey ) : bool
    {
        if ( ! $providerKey->maxAttempts || ! $providerKey->decayMinutes ) return true;
        $result = $this->limiter()->tooManyAttempts( $this->throttleKey( $providerKey ), $providerKey->maxAttempts, $providerKey->decayMinutes );
        $this->setHeaders( $providerKey );
        if ( $result )
        {
            $this->fireLockoutEvent( $request );
        }
        else
        {
            $this->incrementProviderKeyAttempts( $providerKey );
        }
        return $result;
    }

    protected function setHeaders ( ProviderKey $providerKey )
    {
        $maxAttempts = $providerKey->maxAttempts;
        $retryAfter = $this->limiter()->availableIn( $this->throttleKey( $providerKey ) );
        if ( $retryAfter > 0 )
        {
            $remainingAttempts = 0;
            header( 'Retry-After: ' . $retryAfter );
            header( 'X-RateLimit-Reset: ' . ( Carbon::now()->getTimestamp() + $retryAfter ) );
        }
        else
        {
            $remainingAttempts = $this->limiter()->retriesLeft( $this->throttleKey( $providerKey ), $maxAttempts );
        }
        header( 'X-RateLimit-Limit: ' . $maxAttempts );
        header( 'X-RateLimit-Remaining: ' . $remainingAttempts );
    }

    protected function incrementProviderKeyAttempts ( ProviderKey $providerKey )
    {
        $this->limiter()->hit( $this->throttleKey( $providerKey ) );
    }

    protected function clearProviderKeyAttempts ( ProviderKey $providerKey )
    {
        $this->limiter()->clear( $this->throttleKey( $providerKey ) );
    }

    protected function fireLockoutEvent( Request $request )
    {
        event( new Lockout( $request ) );
    }

    protected function throttleKey ( ProviderKey $providerKey )
    {
        return Str::lower( 'throttle.' . $providerKey->api_key );
    }

    protected function limiter ()
    {
        return app(RateLimiter::class);
    }

}
