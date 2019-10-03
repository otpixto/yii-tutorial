<?php

namespace App\Observers;

use App\User;

/**
 * User observer
 */
class UserObserver
{
    const CACHE_LIFE = 360;
    /**
     * @param User $user
     */
    public function saved ( User $user )
    {
        $user->load([
            'managements',
            'providers',
        ]);
        \Cache::put( 'user.' . $user->id, $user, self::CACHE_LIFE );
    }
    /**
     * @param User $user
     */
    public function deleted ( User $user )
    {
        \Cache::forget( 'user.' . $user->id );
    }
    /**
     * @param User $user
     */
    public function restored ( User $user )
    {
        $this->saved( $user );
    }
    /**
     * @param User $user
     */
    public function retrieved ( User $user )
    {
        \Cache::add( 'user.' . $user->id, $user, self::CACHE_LIFE );
    }
}