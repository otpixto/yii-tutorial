<?php

namespace App\Classes;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class MosregClient
{

    const URL = 'https://mosreg.eds-juk.ru';

    private $client = false;

    private $username;
    private $password;

    private $logs;

    public static $answers = [
        4635 => 'Решено. Выявлены нарушения, меры приняты',
        4782 => 'Факты не подтвердились. Повреждений не выявлено',
        4929 => 'Отклонено. Ответ по проблеме предоставлялся ранее',
        5076 => 'Отложено. Ожидается поставка материала',
        5223 => 'Запрос информации. Недостаточно информации для решения проблемы',
        5370 => 'Отклонено. Объект не находится в обслуживании УК',
        5517 => 'Отклонено. Вопрос не в компетенции УК',
    ];

    public function __construct ( $username, $password )
    {
        $this->client = new Client([
            'base_uri' => self::URL,
            RequestOptions::TIMEOUT => 5
        ]);
        $this->username = $username;
        $this->password = $password;
        $this->logs = new Logger( 'MOSREG' );
        $this->logs->pushHandler( new StreamHandler( storage_path( 'logs/mosreg.log' ) ) );
    }

    public function searchAddress ( $term, $normalize = false )
    {
        if ( $normalize )
        {
            $term = $this->normalizeAddress( $term );
        }
        return $this->sendRequest( 'GET', '/api/address/search?term=' . urlencode( $term ) );
    }

    public function getStatuses ()
    {
        return $this->sendRequest( 'GET', '/api/statuses' );
    }

    public function getTickets ( int $page = 1 )
    {
        return $this->sendRequest( 'GET', '/api/tickets?page=' . $page );
    }

    public function getTicket ( int $id )
    {
        return $this->sendRequest( 'GET', '/api/tickets/' . $id );
    }

    public function createTicket ( array $data = [] )
    {
        return $this->sendRequest( 'POST', '/api/tickets/create', $data );
    }

    public function toWork ( $id )
    {
        return $this->sendRequest( 'POST', '/api/tickets/' . $id . '/towork' );
    }

    public function answer ( $id, $answer_id, $comment = null )
    {
        if ( ! isset( self::$answers[ $answer_id ] ) ) return false;
        return $this->sendRequest( 'POST', '/api/tickets/' . $id . '/answer', compact( 'answer_id', 'comment' ) );
    }

    public function setWebhook ( $url )
    {
        return $this->sendRequest( 'POST', '/api/webhook/set', compact( 'url' ) );
    }

    public function unsetWebhook ()
    {
        return $this->sendRequest( 'POST', '/api/webhook/unset' );
    }

    private function sendRequest ( $method, $path, array $data = [] )
    {
        $this->logs->addInfo( 'Request', $data );
        $response = $this->client->request( $method, $path, [
            RequestOptions::AUTH => [
                $this->username,
                $this->password
            ],
            RequestOptions::FORM_PARAMS => $data
        ]);
        $response = json_decode( $response->getBody() );
        $this->logs->addInfo( 'Response', (array) $response );
        return $response;
    }

    public function normalizeAddress ( $address )
    {
        $address = str_replace( ' к. ', 'к', $address );
        $address = preg_replace( '/г\.|ул\.|д\.|\,|Московская обл./iU', '', $address );
        $address = preg_replace( '/\s{2,}/iU', ' ', $address );
        //$address = preg_replace( '/(.* \d+)[а-яa-z]+/iU', '$1', $address );
        $address = trim( $address );
        return $address;
    }

}