<?php

namespace App\Classes;

use App\Models\File;

class MosregRequest
{
    const URL = 'https://mosreg.eds-juk.ru';
    private $method;
    private $path;
    private $data = [];
    public function __construct ( $method, $path, array $data = [] )
    {
        $this->method = $method;
        $this->path = $path;
        foreach ( $data as $name => $value )
        {
            $this->set( $name, $value );
        }
    }
    public function __set ( $name, $value )
    {
        $this->set( $name, $value );
    }
    public function __get ( $name )
    {
        return $this->data[ $name ] ?? null;
    }
    public function __unset ( $name )
    {
        if ( isset( $this->data[ $name ] ) )
        {
            unset( $this->data[ $name ] );
        }
    }
    public function set ( $name, $value )
    {
        if ( $this->method == 'GET' )
        {
            $this->data[ $name ] = $value;
        }
        else
        {
            $this->data[ $name ] = [
                'name'      => $name,
                'contents'  => $value,
            ];
        }
    }
    public function addFile ( File $file )
    {
        if ( $this->method == 'GET' )
        {
            throw new MosregException( 'Добавить файл на методе GET невозможно!' );
        }
        if ( isset( $this->data[ $file->path ] ) )
        {
            throw new MosregException( 'Файл ' . $this->path . ' уже добавлен!' );
        }
        $this->data[ $file->path ] = [
            'name'      => 'files[]',
            'contents'  => $file->getContents(),
            'filename'  => $file->name
        ];
    }
    public function delFile ( File $file )
    {
        if ( isset( $this->data[ $file->path ] ) )
        {
            unset( $this->data[ $file->path ] );
        }
    }
    public function getData ()
    {
        if ( $this->method == 'GET' )
        {
            return [
                \GuzzleHttp\RequestOptions::QUERY => $this->data
            ];
        }
        else
        {
            return [
                \GuzzleHttp\RequestOptions::MULTIPART => array_values( $this->data )
            ];
        }
    }
    public function getMethod ()
    {
        return $this->method;
    }
    public function getUrl ()
    {
        return str_replace( '//', '/', self::URL . '/' . $this->path );
    }
}