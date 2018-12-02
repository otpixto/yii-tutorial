<?php
declare ( strict_types = 1 );

namespace App\Classes;

class Asterisk
{
	
	private $config = [
		'ip' 		=> 'localhost',
		'post' 		=> '5038',
		'user' 		=> 'asterisk',
		'pass' 		=> 'asterisk',
	];

    private $socket = false;
    private $auth = false;

    const LENGTH = 4096;
    const TIMEOUT = 5;
	const EOL = "\r\n";

    public $last_result = null;

    public function __construct ( array $config = [] )
    {
        $this->config = config( 'asterisk' );
        return $this->connect();
    }

    private function connect ( $autologin = true )
    {
        $this->socket = fsockopen( $this->config[ 'ip' ], $this->config[ 'port' ], $errno, $errstr, self::TIMEOUT );
        return $autologin ? $this->login() : ( $this->socket ? true : false );
    }

    private function login ()
    {

        if ( ! $this->socket ) return false;

        $packet = 'Action: login' . self::EOL;
        $packet .= 'Username: ' . $this->config[ 'user' ] . self::EOL;
        $packet .= 'Secret: ' . $this->config[ 'pass' ] . self::EOL;
        $packet .= 'Events: off' . self::EOL . self::EOL;

        $this->write( $packet );

        $this->auth = $this->isSuccess();

        return $this->auth;

    }

    private function logout ()
    {
        if ( ! $this->auth ) return false;
        $packet = 'Action: logoff' . self::EOL . self::EOL;
        $this->write( $packet );
        $this->auth = false;
    }

    private function read ()
    {
		$this->last_result = '';
		$result = '';
		while ( ! feof( $this->socket ) )
		{
			$line = fgets( $this->socket, self::LENGTH );
			$status = socket_get_status( $this->socket );
			if ( $line == self::EOL && ! $status[ 'unread_bytes' ] )
			{
				break;
			}
			else
			{
				$result .= $line;
			}
		}
		$this->last_result = trim( $result );
        return $this->last_result;
    }

    private function write ( $packet )
    {
        fwrite( $this->socket, $packet );
        return $this->read();
    }

    private function isSuccess ( $result = null )
    {
        if ( is_null( $result ) ) $result = $this->last_result;
        return ! empty( $result ) && preg_match( '/response: success/i', $result ) ? true : false;
    }

    public function status ( $channel )
    {

        if ( ! $this->auth ) return false;

        $packet = 'Action: Status' . self::EOL;
        $packet .= 'Channel: ' . $channel . self::EOL . self::EOL;

        $result = $this->write( $packet );

        return $result;

    }
	
    public function originate ( $number_from, $number_to, $callerId = null, $priority = 1 )
    {

        if ( ! $this->auth ) return false;

        $exten = $number_from;
        $channel = $this->prepareChannel( $number_to );
        $context = $this->getContext( $number_to );
		
        $packet = 'Action: originate' . self::EOL;
        $packet .= 'Channel: ' . $channel . self::EOL;
        $packet .= 'Context: ' . $context . self::EOL;
        $packet .= 'Exten: ' . $exten . self::EOL;
        $packet .= 'Priority: ' . $priority . self::EOL;
        $packet .= 'Async: true' . self::EOL;

        if ( ! is_null( $callerId ) )
        {
            $packet .= 'CallerID: ' . $callerId . self::EOL;
        }

        $packet .= self::EOL;

        $this->write( $packet );

        return $this->isSuccess();

    }

    public function hangup ( $number )
    {

        if ( ! $this->auth ) return false;

        $number = $this->prepareNumber( $number );
        $channel = $this->prepareChannel( $number );

        $packet = 'Action: hangup' . self::EOL;
        $packet .= 'Channel: ' . $channel . self::EOL . self::EOL;

        $this->write( $packet );

        return $this->isSuccess();

    }

    public function bridge ( $channel1, $channel2 )
    {

        if ( ! $this->auth ) return false;

        $packet = 'Action: bridge' . self::EOL;
        $packet .= 'Channel1: ' . $channel1 . self::EOL;
        $packet .= 'Channel2: ' . $channel2 . self::EOL . self::EOL;

        $this->write( $packet );

        return $this->isSuccess();

    }

    public function queueAdd ( $number, $queue = null )
    {

        if ( ! $this->auth ) return false;

        $number = $this->prepareNumber( $number );
        $channel = $this->prepareChannel( $number );

        $penalty = 1;
        $paused = 'false';

        if ( is_null( $queue ) )
        {
            $queue = \Config::get( 'asterisk.queue' );
        }

        $packet = 'Action: QueueAdd' . self::EOL;
        $packet .= 'Queue: ' . $queue . self::EOL;
        $packet .= 'Interface: ' . $channel . self::EOL;
        $packet .= 'Penalty: ' . $penalty . self::EOL;
        $packet .= 'Paused: ' . $paused . self::EOL . self::EOL;

        $this->write( $packet );

        return $this->isSuccess();

    }

    public function queueRemove ( $number, $queue = null )
    {

        if ( ! $this->auth ) return false;

        $number = $this->prepareNumber( $number );
        $channel = $this->prepareChannel( $number );

        if ( is_null( $queue ) )
        {
            $queue = \Config::get( 'asterisk.queue' );
        }

        $packet = 'Action: QueueRemove' . self::EOL;
        $packet .= 'Queue: ' . $queue . self::EOL;
        $packet .= 'Interface: ' . $channel . self::EOL . self::EOL;

        $this->write( $packet );

        return $this->isSuccess();

    }

    public function queues ( $parse = false )
    {
        if ( ! $this->auth ) return false;
        $packet = 'Action: Queues' . self::EOL . self::EOL;
        $result = $this->write( $packet );
        if ( ! $parse )
        {
            return $result;
        }
        $exp = explode( "\r\n\r\n", $result );
        $data = [];
        foreach ( $exp as $e )
        {
            preg_match( '/(.*) has/', $e, $matches );
            $queue = trim( $matches[ 1 ] );
            preg_match_all( '/(local|sip)\/(\d*)(.*)\ with\ penalty\ (\d)(.*)(not\ in\ use|busy|ringing)/i', $e, $matches );
            $count = count( $matches[ 0 ] );
            $data[ $queue ] = [
                'list' => [],
                'count' => $count,
                'callers' => 0,
                'busy' => 0
            ];
            for ( $i = 0; $i < $count; $i ++ )
            {
                $isFree = preg_match( '/not\ in\ use/i', $matches[ 6 ][ $i ] );
				$number = mb_substr( $matches[ 2 ][ $i ], -10 );
                $data[ $queue ][ 'list' ][ $number ] = [
                    'penalty' => (int) $matches[ 4 ][ $i ],
                    'isFree' => $isFree ? 1 : 0
                ];
                if ( ! $isFree )
                {
                    $data[ $queue ][ 'busy' ] ++;
                }
            }
            preg_match_all( '/(\d)\.\s(sip|local)/i', $result, $matches );
            ksort( $data[ $queue ][ 'list' ] );
            $data[ $queue ][ 'callers' ] = count( $matches[ 0 ] );
        }
        return $data;
    }

    public function queue ( $queue )
    {
        $queues = $this->queues( true );
        return $queues[ $queue ] ?? null;
    }

    public function redirect ( $channel, $exten, $context = 'default', $priority = 1 )
    {

        if ( ! $this->auth ) return false;

        $packet = 'Action: redirect' . self::EOL;
        $packet .= 'Channel: ' . $channel . self::EOL;
        $packet .= 'Context: ' . $context . self::EOL;
        $packet .= 'Exten: ' . $exten . self::EOL;
        $packet .= 'Priority: ' . $priority . self::EOL . self::EOL;

        $this->write( $packet );

        return $this->isSuccess();

    }

    public function extentionState ( $exten, $context = 'default' )
    {

        if ( ! $this->auth ) return false;

        $packet = 'Action: ExtensionState' . self::EOL;
        $packet .= 'Context: ' . $context . self::EOL;
        $packet .= 'Exten: ' . $exten . self::EOL . self::EOL;

        $result = $this->write( $packet );

        dd( $result );

    }

    public function prepareNumber ( $number )
    {
        $number = mb_substr( preg_replace( '/\D/', '', $number ), -10 );
        if ( mb_strlen( $number ) >= 10 )
        {
            $number = '+7' . mb_substr( $number, -10 );
        }
        return $number;
    }

    public function prepareChannel ( $number )
    {
        $number = mb_substr( preg_replace( '/\D/', '', $number ), -10 );
        $channel = mb_strlen( $number ) >= 10 ? 'SIP/' . $number . '@m9295070506' : 'SIP/' . $number;
        return $channel;
    }

    public function getContext ( $number )
    {
        $number = mb_substr( preg_replace( '/\D/', '', $number ), -10 );
        $context = mb_strlen( $number ) >= 10 ? 'outgoing' : 'default';
        return $context;
    }

    public function __destruct ()
    {
        $this->logout();
        fclose( $this->socket );
    }

}