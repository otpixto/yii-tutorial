<?php

namespace App\Classes;

use App\Models\Provider;
use Mockery\Exception;

class GzhiConfig
{

    public $guid;
    public $username;
    public $password;

    public function __construct ( Provider $provider )
    {
        if ( ! $provider->guid || ! $provider->username || ! $provider->password )
        {
            throw new Exception( 'Некорректые данные' );
        }
        $this->guid = $provider->guid;
        $this->username = $provider->username;
        $this->password = $provider->password;
    }

}