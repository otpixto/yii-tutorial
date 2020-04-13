<?php

namespace App\Providers;

use App\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class CacheUserProvider extends EloquentUserProvider
{
    public function __construct ( HasherContract $hasher )
    {
        parent::__construct( $hasher, User::class );
    }
    public function retrieveById ( $identifier )
    {
        return \Cache::get( 'user.' . $identifier ) ?? parent::retrieveById( $identifier );
    }
}