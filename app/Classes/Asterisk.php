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
        try
        {
            $this->socket = fsockopen( $this->config[ 'ip' ], $this->config[ 'port' ], $errno, $errstr, self::TIMEOUT );
            return $autologin ? $this->login() : ( $this->socket ? true : false );
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    private function read ()
    {
        try
        {
            $this->last_result = '';
            $result = '';
            $startTime = time();
            while ( $line = fgets( $this->socket, self::LENGTH ) )
            {
                $status = socket_get_status( $this->socket );
                if ( ( $line == self::EOL && ! $status[ 'unread_bytes' ] ) || ( time() - $startTime >= self::TIMEOUT ) )
                {
                    break;
                }
                else
                {
                    $result .= $line;
                    usleep( 5000 );
                }
            }
            $this->last_result = trim( $result );
            return $this->last_result;
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    private function write ( $packet )
    {
        try
        {
            if ( is_array( $packet ) )
            {
                $packet = $this->preparePacket( $packet );
            }
            fwrite( $this->socket, $packet );
            return $this->read();
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    private function preparePacket ( array $data = [] )
    {
        try
        {
            $packet = '';
            foreach ( $data as $key => $val )
            {
                $packet .= $key . ': ' . $val . self::EOL;
            }
            $packet .= self::EOL;
            return $packet;
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    private function isSuccess ( $result = null )
    {
        try
        {
            if ( is_null( $result ) ) $result = $this->last_result;
            return ! empty( $result ) && preg_match( '/response: success/i', $result ) ? true : false;
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    private function login ()
    {
        try
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
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    private function logout ()
    {
        try
        {
            if ( ! $this->auth ) return false;
            $this->write([
                'Action'        => 'logoff',
            ]);
            $this->auth = false;
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function status ( $channel )
    {
        try
        {
            if ( ! $this->auth ) return false;
            $result = $this->write([
                'Action'        => 'status',
                'Channel'       => $channel,
            ]);
            return $result;
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }
	
    public function originate ( $number_from, $number_to, $callerId = null, $url = null )
    {
        try
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
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function hangup ( $number )
    {
        try
        {
            if ( ! $this->auth ) return false;
            $channel = $this->prepareChannel( $number );
            $this->write([
                'Action'        => 'hangup',
                'Channel'       => $channel,
            ]);
            return $this->isSuccess();
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function bridge ( $channel1, $channel2 )
    {
        try
        {
            if ( ! $this->auth ) return false;
            $this->write([
                'Action'        => 'bridge',
                'Channel1'      => $channel1,
                'Channel2'      => $channel2,
            ]);
            return $this->isSuccess();
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function queueAddByExten ( $exten, $queue = null )
    {
        try
        {
            if ( ! $this->auth ) return false;
            $channel = $this->prepareChannel( $exten );
            return $this->queueAddByChannel( $channel, $queue );
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function queueAddByChannel ( $channel, $queue = null )
    {
        try
        {
            if ( ! $this->auth ) return false;
            $this->write([
                'Action'        => 'QueueAdd',
                'Queue'         => $queue ?: $this->config[ 'queue' ],
                'Interface'     => $channel,
            ]);
            return $this->isSuccess();
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function queueRemoveByExten ( $exten, $queue = null )
    {
        try
        {
            if ( ! $this->auth ) return false;
            $channel = $this->prepareChannel( $exten );
            return $this->queueRemoveByChannel( $channel, $queue );
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function queueRemoveByChannel ( $channel, $queue = null )
    {
        try
        {
            if ( ! $this->auth ) return false;
            $this->write([
                'Action'        => 'QueueRemove',
                'Queue'         => $queue ?: $this->config[ 'queue' ],
                'Interface'     => $channel,
            ]);
            return $this->isSuccess();
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function queues ( $parse = false )
    {
        try
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
                $pattern = '/(' . preg_quote( $this->config[ 'channel_prefix' ], '/' ) . '(\d*)' . preg_quote( $this->config[ 'channel_postfix' ], '/' ) . '(' . preg_quote( $this->config[ 'channel_postfix_trunc' ], '/' ) . '|)) (.*)(not\ in\ use|in\ use|busy|ringing|in call|unavailable)/i';
                preg_match_all( $pattern, $e, $matches );
                $count = count( $matches[ 0 ] );
                $data[ $queue ] = [
                    'list' => [],
                    'count' => $count,
                    'callers' => 0,
                    'busy' => 0
                ];
                for ( $i = 0; $i < $count; $i ++ )
                {
                    $isBusy = preg_match( '/busy/i', $matches[ 4 ][ $i ] );
                    $channel = $matches[ 1 ][ $i ];
                    $number = mb_substr( $matches[ 2 ][ $i ], -10 );
                    $data[ $queue ][ 'list' ][ $channel ] = [
                        'number'    => $number,
                        'isFree'    => $isBusy ? 0 : 1
                    ];
                    if ( $isBusy )
                    {
                        $data[ $queue ][ 'busy' ] ++;
                    }
                }
                if ( preg_match_all( '/(\d+)\.\s(sip|local)/i', $e, $callers_matches ) )
                {
                    $data[ $queue ][ 'callers' ] = count( $callers_matches[ 0 ] );
                }
                ksort( $data[ $queue ][ 'list' ] );
            }
            return $data;
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function queue ( $queue = null )
    {
        try
        {
            $queues = $this->queues( true );
            return $queues[ $queue ?: $this->config[ 'queue' ] ] ?? null;
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function redirect ( $channel, $number )
    {
        try
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
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function prepareExten ( $number )
    {
        try
        {
            $number = mb_substr( preg_replace( '/\D/', '', $number ), -10 );
            if ( mb_strlen( $number ) == 10 )
            {
                $number = '98' . mb_substr( $number, -10 );
            }
            return $number;
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function prepareChannel ( $number )
    {
        try
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
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function getConfig ( $key )
    {
        try
        {
            return $this->config[ $key ] ?? null;
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

    public function __destruct ()
    {
        try
        {
            $this->logout();
            if ( $this->socket  )
            {
                fclose( $this->socket );
            }
        }
        catch ( \Exception $e )
        {
            $this->last_result = $e->getMessage();
        }
    }

}
