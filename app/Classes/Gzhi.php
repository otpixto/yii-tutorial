<?php

namespace App\Classes;

use Webpatser\Uuid\Uuid;

class Gzhi
{

    private $config;
    private $client;

    public function __construct ( GzhiConfig $config )
    {
        $this->config = $config;
        $this->client = new GzhiSoapClient( $config );
    }

    public function getDate ()
    {
        return date( 'Y-m-d' ) . 'T' . date( 'H:i:s' );
    }

    private function GetData ( array $data = [] )
    {
        return array_merge( [
            'Header' => [
                'OrgGUID' => $this->config->guid,
                'PackGUID' => (string) Uuid::generate(),
                'PackDate' => $this->getDate()
            ]], $data );
    }

    public function GetAddresses ()
    {
        set_time_limit(0);
        $data = $this->GetData([ 'Addresses' => [
            'OrgManager' => true
        ]]);
        $response = $this->client->getNsiDS( $data );
        return $this->GetResult( $response->PackGUID );
    }

    public function GetResult ( $packGUID, int $sleep = 5, int $timeout = 300 )
    {
        $end_time = time() + $timeout;
        $data = $this->GetData([ 'PackGUID' => $packGUID ]);
        $response = $this->client->GetStateDS( $data );
        while ( ! isset( $response->GetNsiResult ) )
        {
            if ( $end_time <= time() )
            {
                return null;
            }
            sleep( $sleep );
            $response = $this->client->GetStateDS( $data );
        }
        return $response->GetNsiResult;
    }

    public function getLastRequest ()
    {
        return $this->client->__getLastRequest();
    }

}