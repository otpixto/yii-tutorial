<?php

namespace App\Classes;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Stream
{

    private $client;

    public function __construct ()
    {
        $this->client = new Client();
    }

    public function send ( $data )
    {
        $this->client->post( \Config::get( 'rest.stream_url' ), [
            RequestOptions::JSON => $data
        ]);
    }

}