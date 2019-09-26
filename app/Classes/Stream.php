<?php

namespace App\Classes;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Stream
{

    private $client;

    public function __construct ()
    {
        try
        {
            $this->client = new Client();
        }
        catch ( \Exception $e )
        {

        }
    }

    public function send ( $data )
    {
        try
        {
            $this->client->post( \Config::get( 'rest.stream_url' ), [
                RequestOptions::JSON => $data
            ]);
        }
        catch ( \Exception $e )
        {

        }
    }

}