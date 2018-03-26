<?php
namespace App\Traits;
use Illuminate\Contracts\Auth\Access\Gate;
trait Authorizable
{
    /**
     * Determine if the entity has a given ability.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function can ( $ability, $arguments = [] ) : bool
    {
        return app( Gate::class )->forUser( $this )->check( $ability, $arguments );
    }
	
    public function canOne ( ... $abilities ) : bool
    {
        foreach ( $abilities as $ability )
        {
            if ( $this->can( $ability ) )
            {
                return true;
            }
        }
        return false;
    }
    /**
     * Determine if the entity does not have a given ability.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function cant ( $ability, $arguments = [] ) : bool
    {
        return ! $this->can( $ability, $arguments );
    }
    /**
     * Determine if the entity does not have a given ability.
     *
     * @param  string  $ability
     * @param  array|mixed  $arguments
     * @return bool
     */
    public function cannot ( $ability, $arguments = [] ) : bool
    {
        return $this->cant($ability, $arguments);
    }
}