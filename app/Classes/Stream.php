<?php

namespace App\Classes;

use App\Models\TicketManagement;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Stream
{

    private $client;

    public function __construct ()
    {
        $this->client = new Client();
    }

    public function send ( $action, TicketManagement $ticketManagement )
    {
        $this->client->post( \Config::get( 'rest.stream_url' ), [
            RequestOptions::JSON => [
                'action' => $action,
                'id' => $ticketManagement->id,
                'ticket_id' => $ticketManagement->ticket_id
            ]
        ]);
    }

}