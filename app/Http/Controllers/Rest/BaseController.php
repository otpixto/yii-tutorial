<?php

namespace App\Http\Controllers\Rest;

use App\Classes\Push;
use App\Http\Controllers\Controller;
use App\Jobs\SendSms;
use App\Models\ProviderKey;
use App\Models\ProviderToken;
use App\Models\SmsAuth;
use App\Traits\LogsTrait;
use App\Traits\ThrottlesProviderKey;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class BaseController extends Controller
{

    use LogsTrait, ThrottlesProviderKey;

    protected $log;

    const TOKEN_SALT = 'ik2a-wVe2<U2E6eS';

    protected $providerKey;
    protected $providerToken;

    protected $sms_auth = false;

    public function __construct ( Request $request )
    {
        \Debugbar::disable();
        $this->addInfo( 'Запрос от ' . $request->ip(), [ $request->method(), $request->path(), $request->all(), $request->server( 'HTTP_REFERER' ) ] );
        ProviderToken
            ::join( 'providers_keys', 'providers_keys.id', '=', 'providers_tokens.provider_key_id' )
            ->whereRaw( '( TIME_TO_SEC( TIMEDIFF( CURRENT_TIMESTAMP, providers_tokens.updated_at ) ) / 60 ) >= providers_keys.token_life' )
            ->delete();
    }

    protected function genToken ( Request $request )
    {
        return md5( $request->server( 'HTTP_USER_AGENT' ) . rand( 1000000, 9999999 ) . microtime() . self::TOKEN_SALT );
    }

    protected function genCode ( $digits = 4 )
    {
        $code = '';
        for ( $i = 0; $i < $digits; $i ++ )
        {
            $code .= rand( 0, 9 );
        }
        return (string) $code;
    }

    protected function checkAuth ( Request $request, & $error = null, & $httpCode = null ) : bool
    {

        if ( ! $this->checkProviderKey( $request, $error, $httpCode ) )
        {
            return false;
        }

        if ( ! \Auth::guard()->attempt( $request->only( $this->credentials ) ) )
        {
            $error = trans('auth.failed' );
            $httpCode = 403;
            return false;
        }

        $user = \Auth::user();

        $token = $this->genToken( $request );

        $providerToken = ProviderToken::create([
            'provider_key_id'       => $this->providerKey->id,
            'user_id'               => $user->id,
            'token'                 => $token,
            'http_user_agent'       => $request->server( 'HTTP_USER_AGENT', '' ),
            'ip'                    => $request->ip(),
        ]);
        if ( $providerToken instanceof MessageBag )
        {
            return false;
        }

        $this->providerToken = $providerToken;

        $providerToken->providerKey->active_at = Carbon::now()->toDateTimeString();
        $providerToken->providerKey->save();

        $user->push_id = $request->get( 'push_id', null );
        $user->save();

        return true;

    }

    protected function checkSmsAuth ( Request $request, & $error = null, & $httpCode = null ) : bool
    {

        if ( ! $this->checkProviderKey( $request, $error, $httpCode ) )
        {
            return false;
        }

        $this->sms_auth = $this->providerKey->provider->sms_auth;
        $token = $this->providerToken->token;

        $phone = $request->get( 'phone' );

        if ( $this->sms_auth )
        {
            if ( $request->get( 'sms_code' ) )
            {
                $code = $request->get( 'sms_code' );
                $smsAuth = SmsAuth
                    ::where( 'phone', '=', $phone )
                    ->where( 'code', '=', $code )
                    ->first();
                if ( $smsAuth )
                {
                    $smsAuth->delete();
                    $this->sms_auth = false;
                    return true;
                }
                else
                {
                    $error = 'Неверный код';
                    return false;
                }
            }
            else
            {
                if ( \Cache::tags( 'rest' )->has( 'sms_auth.' . $phone ) )
                {
                    $error = 'Повторная отправка возможна через ' . Carbon::now()->diffInSeconds( Carbon::createFromTimestamp( \Cache::tags( 'rest' )->get( 'sms_auth.' . $phone ) ) ) . ' сек.';
                    $httpCode = 429;
                    return false;
                }
                else
                {
                    $code = $this->genCode( 4 );
                    SmsAuth
                        ::where( 'phone', '=', $phone )
                        ->delete();
                    $smsAuth = SmsAuth::create(
                        [
                            'phone'         => $phone,
                            'token'         => $token,
                            'code'          => $code,
                            'expired_at'    => Carbon::now()->addSeconds( config( 'sms.alive' ) )->toDateTimeString(),
                        ]
                    );
                    if ( $smsAuth instanceof MessageBag )
                    {
                        $error = 'Пошел в пизду';
                        return false;
                    }
                    $smsAuth->save();
                    $message = 'Код для авторизации: ' . $code;
                    $this->dispatch( new SendSms( $phone, $message ) );
                    \Cache::tags( 'rest' )->put( 'sms_auth.' . $phone, Carbon::now()->addMinute()->timestamp, 1 );
                    return true;
                }
            }
        }
        else
        {
            return true;
        }

    }

    protected function checkProviderKey ( Request $request, & $error = null, & $httpCode = null ) : bool
    {

        if ( empty( $this->providerKey ) )
        {

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
                        ->orWhere( 'ip', 'like', '%' . $request->ip() . '\n' . '%' );
                })
                ->where( function ( $q ) use ( $request )
                {
                    $q
                        ->whereNull( 'referer' );
                    if ( $request->server( 'HTTP_REFERER' ) )
                    {
                        $url = parse_url( $request->server( 'HTTP_REFERER' ) );
                        if ( ! empty( $url[ 'host' ] ) )
                        {
                            $q
                                ->orWhere( 'referer', 'like', '%' . $url[ 'host' ] . '\n' . '%' );
                        }
                    }
                    return $q;
                })
                ->whereHas( 'provider' )
                ->first();

            if ( ! $providerKey )
            {
                $error = 'Некорректный ключ';
                $httpCode = 403;
                return false;
            }

            $providerKey->active_at = Carbon::now()->toDateTimeString();
            $providerKey->save();

            if ( $this->hasTooManyProviderKeyAttempts( $request, $providerKey ) )
            {
                $error = 'Превышено количество запросов в минуту';
                $httpCode = 429;
                return false;
            }

            $this->providerKey = $providerKey;

        }

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
	
	public function check ( Request $request )
    {
        if ( ! $this->checkAll( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }
        return $this->success( 'OK' );
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
            'message'             => 'required|max:255',
            'object'              => 'required|max:255',
            'id'                  => 'required|integer',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }

        $client = new Push( config( 'push.keys.lk' ) );

        $client
            ->setData( $request->all() );

        $response = $client->sendTo( \Auth::user()->push_id );

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

    protected function error ( $error, $httpCode = null ) : Response
    {
        return response( compact( 'error' ), $httpCode ?: 400 );
    }

    protected function success ( $response ) : Response
    {
        return response( $response, 200 );
    }

}
