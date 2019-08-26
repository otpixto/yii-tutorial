<?php

namespace App\Classes;

class Asterisk
{
	
	private $config = [
		'ip' 		                => 'localhost',
		'post' 		                => '5038',
		'user' 		                => 'asterisk',
		'pass' 		                => 'asterisk',
        'queue'                     => 'default',
        'incoming_context'          => 'default',
        'outgoing_context'          => 'default',
        'autodial_context'          => 'default',
        'channel_mask'              => '{{prefix}}{{number}}{{postfix}}',
        'channel_prefix'            => 'SIP/',
        'channel_postfix'           => '',
        'channel_postfix_trunc'     => '',
	];

	private $required = [
        'ip',
        'post',
        'user',
        'pass',
        'queue',
        'incoming_context',
        'outgoing_context',
        'autodial_context',
        'channel_mask',
    ];

    private $socket = false;
    private $auth = false;

    const LENGTH = 1024;
    const TIMEOUT = 5;
	const EOL = "\r\n";

    public $last_result = null;

    public function __construct ( array $config = [] )
    {
        foreach ( $config as $key => $val )
        {
            $this->config[ $key ] = $val;
            if ( in_array( $key, $this->required ) && empty( $this->config[ $key ] ) )
            {
                throw new \Exception( 'Config ' . $key . ' is required' );
            }
        }
        return $this->connect();
    }

    private function connect ( $autologin = true )
    {
        $this->socket = fsockopen( $this->config[ 'ip' ], $this->config[ 'port' ], $errno, $errstr, self::TIMEOUT );
        return $autologin ? $this->login() : ( $this->socket ? true : false );
    }

    private function read ()
    {
		$this->last_result = '';
		$result = '';
		while ( $line = fgets( $this->socket, self::LENGTH ) )
		{
            $status = socket_get_status( $this->socket );
            if ( $line == self::EOL && ! $status[ 'unread_bytes' ] )
            {
                break;
            }
            else
            {
                $result .= $line;
                usleep( 200 );
            }
		}
		$this->last_result = trim( $result );
        return $this->last_result;
    }

    private function write ( $packet )
    {
        if ( is_array( $packet ) )
        {
            $packet = $this->preparePacket( $packet );
        }
        fwrite( $this->socket, $packet );
        return $this->read();
    }

    private function preparePacket ( array $data = [] )
    {
        $packet = '';
        foreach ( $data as $key => $val )
        {
            $packet .= $key . ': ' . $val . self::EOL;
        }
        $packet .= self::EOL;
        return $packet;
    }

    private function isSuccess ( $result = null )
    {
        if ( is_null( $result ) ) $result = $this->last_result;
        return ! empty( $result ) && preg_match( '/response: success/i', $result ) ? true : false;
    }

    private function login ()
    {
        if ( ! $this->socket ) return false;
        $this->write([
            'Action'        => 'login',
            'Username'      => $this->config[ 'user' ],
            'Secret'        => $this->config[ 'pass' ],
            'Events'        => 'off',
        ]);
        $this->auth = $this->isSuccess();
        return $this->auth;
    }

    private function logout ()
    {
        if ( ! $this->auth ) return false;
        $this->write([
            'Action'        => 'logoff',
        ]);
        $this->auth = false;
    }

    public function status ( $channel )
    {
        if ( ! $this->auth ) return false;
        $result = $this->write([
            'Action'        => 'status',
            'Channel'       => $channel,
        ]);
        return $result;
    }
	
    public function originate ( $number_from, $number_to, $callerId = null, $url = null )
    {
        if ( ! $this->auth ) return false;
        $channel = $this->prepareChannel( $number_from );
        $exten = $this->prepareExten( $number_to );
        $packet = [
            'Action'        => 'originate',
            'Channel'       => $channel,
            'Context'       => $this->config[ 'autodial_context' ],
            'Exten'         => $exten,
            'Async'         => 'true',
        ];
        if ( $callerId )
        {
            $packet[ 'CallerID' ] = $callerId;
        }
        if ( $url )
        {
            $packet[ 'Variable' ] = 'url=' . $url;
        }
        $this->write( $packet );
        return $this->isSuccess();
    }

    public function hangup ( $number )
    {
        if ( ! $this->auth ) return false;
        $channel = $this->prepareChannel( $number );
        $this->write([
            'Action'        => 'hangup',
            'Channel'       => $channel,
        ]);
        return $this->isSuccess();
    }

    public function bridge ( $channel1, $channel2 )
    {
        if ( ! $this->auth ) return false;
        $this->write([
            'Action'        => 'bridge',
            'Channel1'      => $channel1,
            'Channel2'      => $channel2,
        ]);
        return $this->isSuccess();
    }

    public function queueAddByExten ( $exten, $queue = null )
    {
        if ( ! $this->auth ) return false;
        $channel = $this->prepareChannel( $exten );
        return $this->queueAddByChannel( $channel, $queue );
    }

    public function queueAddByChannel ( $channel, $queue = null )
    {
        if ( ! $this->auth ) return false;
        $this->write([
            'Action'        => 'QueueAdd',
            'Queue'         => $queue ?: $this->config[ 'queue' ],
            'Interface'     => $channel,
        ]);
        return $this->isSuccess();
    }

    public function queueRemoveByExten ( $exten, $queue = null )
    {
        if ( ! $this->auth ) return false;
        $channel = $this->prepareChannel( $exten );
        return $this->queueRemoveByChannel( $channel, $queue );
    }

    public function queueRemoveByChannel ( $channel, $queue = null )
    {
        if ( ! $this->auth ) return false;
        $this->write([
            'Action'        => 'QueueRemove',
            'Queue'         => $queue ?: $this->config[ 'queue' ],
            'Interface'     => $channel,
        ]);
        return $this->isSuccess();
    }

    public function queues ( $parse = false )
    {
        if ( ! $this->auth ) return false;
        $result = $this->write([
            'Action'        => 'Queues',
        ]);
        if ( ! $parse )
        {
            return $result;
        }
        $exp = explode( self::EOL . self::EOL, $result );
        $data = [];
        foreach ( $exp as $e )
        {
            preg_match( '/(.*) has/', $e, $matches );
            $queue = trim( $matches[ 1 ] );
            preg_match_all( '/((local|sip)\/(\d*)(@\d*|)) (.*)(not\ in\ use|busy|ringing|in call|unavailable)/i', $e, $matches );
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
                $channel = $matches[ 1 ][ $i ];
                $number = mb_substr( $matches[ 3 ][ $i ], -10 );
                $data[ $queue ][ 'list' ][ $channel ] = [
                    'prefix'    => $matches[ 2 ][ $i ],
                    'number'    => $number,
                    'postfix'   => $matches[ 4 ][ $i ],
                    'isFree'    => $isFree ? 1 : 0
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

    public function queue ( $queue = null )
    {
        $queues = $this->queues( true );
        return $queues[ $queue ?: $this->config[ 'queue' ] ] ?? null;
    }

    public function redirect ( $channel, $number )
    {
        if ( ! $this->auth ) return false;
        $exten = $this->prepareExten( $number );
        $this->write([
            'Action'        => 'redirect',
            'Channel'       => $channel,
            'Context'       => $this->config[ 'incoming_context' ],
            'Exten'         => $exten,
            'Priority'      => 1,
        ]);
        return $this->isSuccess();
    }

    public function prepareExten ( $number )
    {
        $number = mb_substr( preg_replace( '/\D/', '', $number ), -10 );
        if ( mb_strlen( $number ) == 10 )
        {
            $number = '98' . mb_substr( $number, -10 );
        }
        return $number;
    }

    public function prepareChannel ( $number )
    {
        $number = mb_substr( preg_replace( '/\D/', '', $number ), -10 );
        $channel = $this->config[ 'channel_mask' ];
        $channel = str_replace( '{{prefix}}', $this->config[ 'channel_prefix' ], $channel );
        if ( mb_strlen( $number ) == 10 )
        {
			$channel = str_replace( '{{number}}', '8' . $number, $channel );
            $channel = str_replace( '{{postfix}}', $this->config[ 'channel_postfix_trunc' ], $channel );
        }
        else
        {
			$channel = str_replace( '{{number}}', $number, $channel );
            $channel = str_replace( '{{postfix}}', $this->config[ 'channel_postfix' ], $channel );
        }
        return $channel;
    }

    public function getConfig ( $key )
    {
        return $this->config[ $key ] ?? null;
    }

    public function __destruct ()
    {
        $this->logout();
        if ( $this->socket  )
        {
            fclose( $this->socket );
        }
    }

}