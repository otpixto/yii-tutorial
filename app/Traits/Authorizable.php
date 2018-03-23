<?php

namespace App\Traits;

use Iphome\Permission\Exceptions\PermissionDoesNotExist;

trait Authorizable
{

    public function can ( $permission ) : bool
    {
        try
        {
            return $this->hasPermissionTo( $permission, $this->getGuard() );
        }
        catch ( PermissionDoesNotExist $e )
        {
            return false;
        }
    }

    public function canOne ( ... $permissions ) : bool
    {
        foreach ( $permissions as $permission )
        {
            if ( $this->can( $permission ) )
            {
                return true;
            }
        }
        return false;
    }

    public function cant ( $permission ) : bool
    {
        return ! $this->can( $permission );
    }

    public function cannot ( $permission ) : bool
    {
        return $this->cant( $permission );
    }

}
