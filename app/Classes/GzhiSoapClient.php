<?php

namespace App\Classes;

class GzhiSoapClient extends \SoapClient
{

    private $last_request;

    public function __construct ( GzhiConfig $config )
    {
        $wsdl = \Config::get( 'gzhi.wsdl' );
        $options = [
            'location' => \Config::get( 'gzhi.url' ),
            'login' => $config->username,
            'password' => $config->password,
            'connection_timeout' => 30,
            'trace' => 1
        ];
        return parent::__construct( $wsdl, $options );
    }

    public function __doRequest ( $request, $location, $action, $version, $one_way = 0 )
    {
        $request = preg_replace('/<(ns(\d)):(\w+)/', '<$1:$3 $1:version="' . \Config::get( 'gzhi.version' ) . '"', $request, 1);
        $this->last_request = $request;
        return parent::__doRequest( $request, $location, $action, $version, $one_way );
    }

    public function __getLastRequest ()
    {
        return $this->last_request;
    }

}