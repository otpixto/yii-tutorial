<?php

namespace App\Classes;

use App\Models\Region;
use Mockery\Exception;

class GzhiConfig
{

    public $guid;
    public $username;
    public $password;

    public function __construct ( Region $region )
    {
        if ( ! $region->guid || ! $region->username || ! $region->password )
        {
            throw new Exception( 'Некорректые данные' );
        }
        $this->guid = $region->guid;
        $this->username = $region->username;
        $this->password = $region->password;
    }

}