<?php

namespace App\Classes;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class MosregClient
{

    const URL = 'https://mosreg.eds-juk.ru';
    const TIMEOUT = 10;

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
            'base_uri'              => self::URL,
            RequestOptions::TIMEOUT => self::TIMEOUT
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
        $data = [
            'term' => $term,
        ];
        return $this->sendRequest( 'GET', '/api/address/search', $data );
    }

    public function getStatuses ()
    {
        return $this->sendRequest( 'GET', '/api/statuses' );
    }

    public function getTickets ( int $page = 1 )
    {
        $data = [
            'page' => $page,
        ];
        return $this->sendRequest( 'GET', '/api/tickets', $data );
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

    public function answer ( $id, $answer_id, $comment = '-', Collection $files = null )
    {
        if ( ! isset( self::$answers[ $answer_id ] ) )
        {
            throw new MosregException( 'Некорректный ID ответа' );
        }
        $data = [
            'answer_id'     => $answer_id,
            'comment'       => $comment,
        ];
        return $this->sendRequest( 'POST', '/api/tickets/' . $id . '/answer', $data, $files );
    }

    public function setWebhook ( $url )
    {
        $data = [
            'url' => $url,
        ];
        return $this->sendRequest( 'POST', '/api/webhook/set', $data );
    }

    public function unsetWebhook ()
    {
        return $this->sendRequest( 'POST', '/api/webhook/unset' );
    }

    public function changePassword ( $password )
    {
        $data = [
            'password' => $password,
        ];
        return $this->sendRequest( 'POST', '/api/password/change', $data );
    }

    private function sendRequest ( $method, $path, array $data = null, Collection $files = null )
    {
        $this->logs->addInfo( 'Request', compact( 'method', 'path', 'data' ) );
        $requestOptions = [
            RequestOptions::AUTH => [
                $this->username,
                $this->password
            ],
        ];
        if ( $method == 'GET' )
        {
            if ( $data )
            {
                $requestOptions[ RequestOptions::QUERY ] = $data;
            }
        }
        else
        {
            $requestData = [];
            if ( $data )
            {
                foreach ( $data as $name => $value )
                {
                    $requestData[] = [
                        'name'      => $name,
                        'contents'  => $value,
                    ];
                }
            }
            if ( $files && $files->count() )
            {
                foreach ( $files as $file )
                {
                    $requestData[] = [
                        'name'          => 'files[]',
                        'contents'      => fopen( storage_path( 'app/' . $file->path ), 'r' ),
                        'filename'      => $file->name,
                    ];
                }
            }
            if ( count( $requestData ) )
            {
                $requestOptions[ RequestOptions::MULTIPART ] = $requestData;
            }
        }
        $response = $this->client->request( $method, $path, $requestOptions );
        $responseData = json_decode( $response->getBody() );
        $this->logs->addInfo( 'Response', (array) $responseData );
        return $responseData;
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
