<?php
declare ( strict_types = 1 );

namespace App\Classes;

class Asterisk
{

    private $asterisk_host;
    private $asterisk_port;
    private $asterisk_user;
    private $asterisk_pass;

    private $socket = false;
    private $auth = false;

    private $size = 3000;
    private $timeout = 10;

    public $last_result = null;

    public function __construct ()
    {
        $this->asterisk_host = \Config( 'asterisk.ip' );
        $this->asterisk_port = \Config( 'asterisk.port' );
        $this->asterisk_user = \Config( 'asterisk.user' );
        $this->asterisk_pass = \Config( 'asterisk.pass' );
        return $this->connect();
    }

    private function connect ( $autologin = true )
    {
        $this->socket = fsockopen( $this->asterisk_host, $this->asterisk_port, $errno, $errstr, $this->timeout );
        return $autologin ? $this->login() : ( $this->socket ? true : false );
    }

    private function login ()
    {

        if ( !$this->socket ) return false;

        // Пропускаем приветствие
        $this->read();

        $packet = 'Action: login' . PHP_EOL;
        $packet .= 'Username: ' . $this->asterisk_user . PHP_EOL;
        $packet .= 'Secret: ' . $this->asterisk_pass . PHP_EOL;
        $packet .= 'Events: off' . PHP_EOL . PHP_EOL;

        $this->write( $packet );

        $this->auth = $this->isSuccess();

        return $this->auth;

    }

    private function logout ()
    {
        $packet = 'Action: logoff' . PHP_EOL . PHP_EOL;
        $this->write( $packet );
        $this->auth = false;
    }

    private function read ()
    {
        usleep( 500000 ); //полсекунды
        $result = fread( $this->socket, $this->size );
        $this->last_result = $result;
        return $result;
    }

    private function write ( $packet )
    {
        fwrite( $this->socket, $packet );
        return $this->read();
    }

    private function isSuccess ( $result = null )
    {
        if ( is_null( $result ) ) $result = $this->last_result;
        return !empty( $result ) && preg_match( '/response: success/i', $result ) ? true : false;
    }

    /*
    Exten: Название екстеншена, статус которого проверяем.
    Context: Контекст, где находиться екстеншен.
    ActionID: Необязательный ID команды, который будет возвращен в ответе.
    */

    public function status ( $channel )
    {

        if ( !$this->auth ) return false;

        $packet = 'Action: Status' . PHP_EOL;
        $packet .= 'Channel: ' . $channel . PHP_EOL . PHP_EOL;

        $result = $this->write( $packet );

        return $result;

    }

    /*
    number - номер для вызова
    callerId - отображаемых номер
    */

    public function originate ( $number, $callerId = null, $priority = 1 )
    {

        if ( !$this->auth ) return false;

        $context = $this->getContext( $number );

        $number = $this->prepareNumber( $number );
        $channel = $this->prepareChannel( $number );

        $packet = 'Action: originate' . PHP_EOL;
        $packet .= 'Channel: ' . $channel . PHP_EOL;
        $packet .= 'Context: ' . $context . PHP_EOL;
        $packet .= 'Exten: ' . $number . PHP_EOL;
        $packet .= 'Priority: ' . $priority . PHP_EOL;
        $packet .= 'Async: true' . PHP_EOL;

        if ( !is_null( $callerId ) )
        {
            $packet .= 'CallerID: ' . $callerId . PHP_EOL;
        }

        $packet .= PHP_EOL;

        $this->write( $packet );

        return $this->isSuccess();

    }

    public function connectTwo ( $number1, $number2 )
    {

        return ( $this->originate( $number2, $number1 ) && $this->originate( $number1, $number2 ) && $this->bridge( $number1, $number2 ) );

    }

    public function hangup ( $number )
    {

        $number = $this->prepareNumber( $number );
        $channel = $this->prepareChannel( $number );

        $packet = 'Action: hangup' . PHP_EOL;
        $packet .= 'Channel: ' . $channel . PHP_EOL . PHP_EOL;

        $this->write( $packet );

        return $this->isSuccess();

    }

    public function bridge ( $number1, $number2 )
    {

        $number1 = $this->prepareNumber( $number1 );
        $number2 = $this->prepareNumber( $number2 );
        $channel1 = $this->prepareChannel( $number1 );
        $channel2 = $this->prepareChannel( $number2 );

        $packet = 'Action: bridge' . PHP_EOL;
        $packet .= 'Channel1: ' . $channel1 . PHP_EOL;
        $packet .= 'Channel2: ' . $channel2 . PHP_EOL . PHP_EOL;

        $this->write( $packet );

        return $this->isSuccess();

    }

    public function queueAdd ( $number, $queue = null )
    {

        $number = $this->prepareNumber( $number );
        $channel = $this->prepareChannel( $number );

        $penalty = 1;
        $paused = 'false';

        if ( is_null( $queue ) )
        {
            $queue = \Config::get( 'asterisk.queue' );
        }

        $packet = 'Action: QueueAdd' . PHP_EOL;
        $packet .= 'Queue: ' . $queue . PHP_EOL;
        $packet .= 'Interface: ' . $channel . PHP_EOL;
        $packet .= 'Penalty: ' . $penalty . PHP_EOL;
        $packet .= 'Paused: ' . $paused . PHP_EOL . PHP_EOL;

        $this->write( $packet );

        return $this->isSuccess();

    }

    public function queueRemove ( $number, $queue = null )
    {

        $number = $this->prepareNumber( $number );
        $channel = $this->prepareChannel( $number );

        if ( is_null( $queue ) )
        {
            $queue = \Config::get( 'asterisk.queue' );
        }

        $packet = 'Action: QueueRemove' . PHP_EOL;
        $packet .= 'Queue: ' . $queue . PHP_EOL;
        $packet .= 'Interface: ' . $channel . PHP_EOL . PHP_EOL;

        $this->write( $packet );

        return $this->isSuccess();

    }

    /*
     * return Array
     * list = [ penalty, isBusy ]
     */

    public function queues ( $parse = false )
    {
        $packet = 'Action: QueueStatus' . PHP_EOL . PHP_EOL;
        $result = $this->write( $packet );
        dd( $result );
        if ( $parse )
        {
            preg_match_all( '/(local|sip)\/(\d*)\ with\ penalty\ (\d)(.*)(not\ in\ use|busy|ringing)/i', $result, $matches );
            $count = count( $matches[ 0 ] );
            $data = [
                'list' => [],
                'count' => $count,
                'callers' => 0,
                'busy' => 0
            ];
            for ( $i = 0; $i < $count; $i ++ )
            {
                $isFree = preg_match( '/not\ in\ use/i', $matches[ 5 ][ $i ] );
                $data[ 'list' ][ $matches[ 2 ][ $i ] ] = [ (int) $matches[ 3 ][ $i ], $isFree ? 0 : 1 ];
                if ( ! $isFree )
                {
                    $data[ 'busy' ] ++;
                }
            }
            preg_match_all( '/(\d)\.\s(sip|local)/i', $result, $matches );
            ksort( $data[ 'list' ] );
            $data[ 'callers' ] = count( $matches[ 0 ] );
            return $data;
        }
        return $result;
    }

    public function queue ( $queue )
    {
        $queues = $this->queues( true );
        return $queues[$queue] ? $queues[$queue] : null;
    }

    public function redirect ( $channel, $exten, $context = 'default', $priority = 1 )
    {

        if ( !$this->auth ) return false;

        $packet = 'Action: redirect' . PHP_EOL;
        $packet .= 'Channel: ' . $channel . PHP_EOL;
        $packet .= 'Context: ' . $context . PHP_EOL;
        $packet .= 'Exten: ' . $exten . PHP_EOL;
        $packet .= 'Priority: ' . $priority . PHP_EOL . PHP_EOL;

        $this->write( $packet );

        return $this->isSuccess();

    }

    public function extentionState ( $exten, $context = 'default' )
    {

        if ( !$this->auth ) return false;

        $packet = 'Action: ExtensionState' . PHP_EOL;
        $packet .= 'Context: ' . $context . PHP_EOL;
        $packet .= 'Exten: ' . $exten . PHP_EOL . PHP_EOL;

        $result = $this->write( $packet );

        dd( $result );

    }

    public function prepareNumber ( $number )
    {
        if ( mb_strlen( $number ) >= 10 )
        {
            $number = '98' . mb_substr( $number, -10 );
        }
        return $number;
    }

    public function prepareChannel ( $number )
    {
        $channel = mb_strlen( $number ) >= 10 ? 'LOCAL/' . $number . '@outgoing' : 'SIP/' . $number;
        return $channel;
    }

    public function getContext ( $number )
    {
        $context = mb_strlen( $number ) >= 10 ? 'outgoing' : 'default';
        return $context;
    }

    public function __destruct ()
    {
        $this->logout();
        fclose( $this->socket );
    }

}