<?php

namespace App\Exceptions;

use Exception;

class InactiveException extends Exception
{

    /**
     * Create a new authentication exception.
     *
     * @param  string  $message
     * @param  array  $guards
     * @return void
     */
    public function __construct($message = 'Учетная запись неактивна.')
    {
        parent::__construct($message);
    }

}
