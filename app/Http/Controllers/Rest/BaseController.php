<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\ProviderKey;
use App\Models\ProviderToken;
use App\Traits\LogsTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class BaseController extends Controller
{

    use LogsTrait;

    protected $log;

    const TOKEN_SALT = 'ik2a-wVe2<U2E6eS';

    const PUSH_API_URL = 'https://android.googleapis.com/gcm/send';
    const PUSH_API_KEY = 'AAAAmGZOAw8:APA91bEkYaBLgTk_ZfYc5DArBBF6Jh6C8NHObhmf3UfOknAtfvry1AQXQ2miUrLEk4Xfmv2wp6IgP7_usWsyZjzOXArH-qG_kNoiPcriHIQfPEGGw1fEkafqvImEkN_unGeWglGCHUpO';

    protected $providerKey;
    protected $providerToken;

    public function __construct ( Request $request )
    {
        \Debugbar::disable();
        $this->addInfo( 'Запрос от ' . $request->ip(), [ $request->path(), $request->all() ] );
        ProviderToken
            ::join( 'providers_keys', 'providers_keys.id', '=', 'providers_tokens.provider_key_id' )
            ->whereRaw( '( TIME_TO_SEC( TIMEDIFF( CURRENT_TIMESTAMP, providers_tokens.updated_at ) ) / 60 ) >= providers_keys.token_life' )
            ->delete();
    }

    protected function genToken ( Request $request )
    {
        return md5( $request->server( 'HTTP_USER_AGENT' ) . rand( 1000000, 9999999 ) . microtime() . self::TOKEN_SALT );
    }

    protected function checkProviderKey ( Request $request, & $error = null, & $httpCode = null ) : bool
    {

        if ( ! empty( $this->providerKey ) )
        {
            return true;
        }

        $validation = \Validator::make( $request->all(), [
            'api_key'         => 'required',
        ]);

        if ( $validation->fails() )
        {
            $error = $validation->errors()->first();
            $httpCode = 400;
            return false;
        }

        $providerKey = ProviderKey
            ::where( 'api_key', '=', $request->get( 'api_key' ) )
            ->where( function ( $q ) use ( $request )
            {
                return $q
                    ->whereNull( 'ip' )
                    ->orWhere( 'ip', 'like', '%' . $request->ip() . '%' );
            })
            ->whereHas( 'provider' )
            ->first();

        if ( ! $providerKey )
        {
            $error = 'Некорректный ключ';
            $httpCode = 403;
            return false;
        }

        $this->providerKey = $providerKey;

        return true;

    }

    protected function checkProviderToken ( Request $request, & $error = null, & $httpCode = null ) : bool
    {

        if ( ! empty( $this->providerToken ) )
        {
            return true;
        }

        $validation = \Validator::make( $request->all(), [
            'token'         => 'required',
        ]);

        if ( $validation->fails() )
        {
            $error = $validation->errors()->first();
            $httpCode = 400;
            return false;
        }

        $providerToken = ProviderToken
            ::where( 'provider_key_id', '=', $this->providerKey->id )
            ->where( 'token', '=', $request->get( 'token' ) )
            ->where( 'http_user_agent', '=', $request->server( 'HTTP_USER_AGENT', '' ) )
            ->whereHas( 'providerKey', function ( $providerKey )
            {
                return $providerKey
                    ->whereHas( 'provider' );
            })
            ->first();

        if ( ! $providerToken )
        {
            $error = 'Некорректный токен';
            $httpCode = 403;
            return false;
        }

        $this->providerToken = $providerToken;
        $this->providerToken->updated_at = Carbon::now()->toDateTimeString();
        $this->providerToken->providerKey->active_at = Carbon::now()->toDateTimeString();
        $this->providerToken->ip = $request->ip();
        $this->providerToken->save();
        $this->providerToken->providerKey->save();

        \Auth::login( $this->providerToken->user );

        return true;

    }

    protected function checkUser ( & $error = null, & $httpCode = null ) : bool
    {
        $user = \Auth::user();
        if ( ! $user || ! $user->active )
        {
            $error = 'Пользователь не активен';
            $httpCode = 403;
            return false;
        }
        else
        {
            return true;
        }
    }

    protected function checkAll ( Request $request, & $error = null, & $httpCode = null ) : bool
    {
        if ( ! $this->checkProviderKey( $request, $error, $httpCode ) )
        {
            return false;
        }
        if ( ! $this->checkProviderToken( $request, $error, $httpCode ) )
        {
            return false;
        }
        if ( ! $this->checkUser( $error, $httpCode ) )
        {
            return false;
        }
        return true;
    }

    public function logout ( Request $request )
    {
        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }
        $this->providerToken->delete();
        return $this->success( 'Bye-bye!' );
    }

    public function push ( Request $request )
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        if ( ! \Auth::user()->push_id )
        {
            return $this->error( 'Отсутствует PUSH-токен' );
        }

        $validation = \Validator::make( $request->all(), [
            'title'               => 'required|max:255',
            'body'                => 'required|max:255',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }

        $notification = [
            'title'     => $request->get( 'title' ),
            'body'      => $request->get( 'body' ),
        ];

        $request = [
            'to'            => \Auth::user()->push_id,
            'notification'  => $notification,
        ];

        $headers = [
            'Authorization: key=' . self::PUSH_API_KEY,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, self::PUSH_API_URL );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $request ) );
        $response = curl_exec( $ch );
        curl_close( $ch );

        $this->addLog( 'Отправил PUSH-уведомление' );

        return $this->success( $response );

    }

    public function sessions ( Request $request )
    {
        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }
        $providerTokens = ProviderToken
            ::where( 'provider_key_id', '=', $this->providerToken->provider_key_id )
            ->where( 'user_id', '=', $this->providerToken->user_id )
            ->where( 'id', '!=', $this->providerToken->id )
            ->get();
        $response = [];
        foreach ( $providerTokens as $providerToken )
        {
            $response[] = [
                'id'                    => (int) $providerToken->id,
                'http_user_agent'       => $providerToken->http_user_agent,
                'created_at'            => $providerToken->created_at->timestamp,
            ];
        }
        return $this->success( $response );
    }

    public function sessionsClose ( Request $request )
    {
        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }
        ProviderToken
            ::where( 'provider_key_id', '=', $this->providerToken->provider_key_id )
            ->where( 'user_id', '=', $this->providerToken->user_id )
            ->where( 'id', '!=', $this->providerToken->id )
            ->delete();
        return $this->success( 'OK' );
    }

    public function changePassword ( Request $request )
    {

        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        $validation = \Validator::make( $request->all(), [
            'password'              => 'required|min:5|max:50',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }

        \Auth::user()->changePass( $request->get( 'password' ) );

        return $this->success( 'OK' );

    }

    protected function setLogs ( $path )
    {
        $this->log = new Logger( 'REST' );
        $this->log->pushHandler( new StreamHandler( $path ) );
    }

    protected function addInfo ( $text, $data = null )
    {
        if ( ! isset( $this->log ) ) return;
        return $this->log->addInfo( $text, $data );
    }

    protected function addError ( $text, $data = null )
    {
        if ( ! isset( $this->log ) ) return;
        return $this->log->addError( $text, $data );
    }

    protected function addCritical ( $text, $data = null )
    {
        if ( ! isset( $this->log ) ) return;
        return $this->log->addCritical( $text, $data );
    }

    protected function error ( $error, $httpCode = 400 ) : Response
    {
        return response( compact( 'error' ), $httpCode );
    }

    protected function success ( $response ) : Response
    {
        return response( $response, 200 );
    }

}
