<?php

namespace App\Classes;

class Push
{

    private $curl;

    public $title = null;
    public $body = null;

    public $data = [];

    public function __construct ( $apiKey )
    {
        $headers = [
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json'
        ];
        $this->curl = curl_init();
        curl_setopt( $this->curl,CURLOPT_URL, config( 'push.url' ) );
        curl_setopt( $this->curl,CURLOPT_POST, true );
        curl_setopt( $this->curl,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $this->curl,CURLOPT_SSL_VERIFYPEER, true );
        curl_setopt( $this->curl,CURLOPT_HTTPHEADER, $headers );
    }

    public function setTitle ( $title )
    {
        $this->title = $title;
        return $this;
    }

    public function setBody ( $body )
    {
        $this->body = $body;
        return $this;
    }

    public function setData ( $key, $value )
    {
        $this->data[ $key ] = $value;
        return $this;
    }

    public function __set ( $key, $value )
    {
        if ( ! isset( $this->$key ) )
        {
            $this->setData( $key, $value );
        }
        return $this;
    }

    public function sendTo ( $token )
    {
        if ( ! $this->curl ) return false;
        $notification = [
            'title'                 => $this->title,
            'body'                  => $this->body,
        ];
        $request = [
            'to'                    => $token,
            'notification'          => $notification,
            //'data'                  => $this->data
        ];
        curl_setopt( $this->curl,CURLOPT_POSTFIELDS, json_encode( $request ) );
        $response = curl_exec( $this->curl );
        return $response;
    }

    public function __destruct ()
    {
        if ( $this->curl )
        {
            curl_close( $this->curl );
        }
    }

}