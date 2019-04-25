<?php

namespace App\Classes;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class Mosreg
{

    const URL = 'http://mosreg.eds-juk.ru';

    private $client = false;

    public $id;
    private $username;
    private $password;

    public function __construct ( $id, $username, $password )
    {
        $this->client = new Client([
            'base_uri' => self::URL,
            RequestOptions::TIMEOUT => 5
        ]);
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
    }

    public function searchAddress ( $q )
    {
        return $this->sendRequest( 'GET', '/api/address/search?company_id=' . $this->id . '&q=' . urlencode( $q ) );
    }

    public function getStatuses ()
    {
        return $this->sendRequest( 'GET', '/api/statuses' );
    }

    public function getTickets ( int $page = 1 )
    {
        return $this->sendRequest( 'GET', '/api/tickets?company_id=' . $this->id . '&page=' . $page );
    }

    public function getTicket ( int $id )
    {
        return $this->sendRequest( 'GET', '/api/tickets/' . $id . '/?company_id=' . $this->id );
    }

    public function createTicket ( array $data = [] )
    {
        return $this->sendRequest( 'POST', '/api/tickets/create/?company_id=' . $this->id, $data );
    }

    public function changeStatus ( $id, $status_code )
    {
        return $this->sendRequest( 'POST', '/api/tickets/' . $id . '/status?company_id=' . $this->id, compact( 'status_code' ) );
    }

    private function sendRequest ( $method, $path, array $data = [] )
    {
        $response = $this->client->request( $method, $path, [
            RequestOptions::AUTH => [
                $this->username,
                $this->password
            ],
            RequestOptions::FORM_PARAMS => $data
        ]);
        return json_decode( $response->getBody() );
    }

}