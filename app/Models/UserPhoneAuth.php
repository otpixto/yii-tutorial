<?php
declare ( strict_types = 1 );

namespace App\Models;

use App\Classes\Asterisk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;

class UserPhoneAuth extends BaseModel
{

    protected $table = 'users_phone_auth';
    public static $_table = 'users_phone_auth';

    protected $primaryKey = 'id';

    public static $name = 'Авторизация на телефоне';

    private static $code_length = 3; // длина кода авторизации
    public static $timeout = 30; // таймаут авторизации

    protected $nullable = [
        'provider_id'
    ];

    protected $fillable = [
        'provider_id',
        'user_id',
        'number',
        'code'
    ];
    private static $asterisk;

    public function __construct ( array $attributes = [] )
    {
        parent::__construct( $attributes );
        $this->where( 'created_at', '<=', Carbon::now()->subSeconds( self::$timeout )->toDateTimeString() )->delete();
    }

    public static function create ( array $attributes = [] )
    {

        UserPhoneAuth
            ::where( 'user_id', '=', \Auth::user ()->id )
            ->orWhere( 'number', '=', $attributes['number'] )
            ->delete();

        $attributes[ 'code' ] = self::genCode();
        $attributes[ 'user_id' ] = \Auth::user()->id;

        return parent::create( $attributes );

    }

    public static function confirm ( array $attributes = [] )
    {

        $rules = [
            'number'        => 'required|min:2',
            'code'          => 'required|digits:' . self::$code_length
        ];

        $v = Validator::make( $attributes, $rules );
        if ( $v->fails() ) return $v->messages();

        $phoneAuth = UserPhoneAuth
            ::where( 'number', '=', $attributes[ 'number' ] )
            ->where( 'code', '=', $attributes[ 'code' ] )
            ->first();

        if ( ! $phoneAuth )
        {
            return new MessageBag( [ 'Неверный код' ] );
        }

        $asterisk = Provider::getCurrent()->getAsterisk();

        if ( ! $asterisk->queueAddByExten( $phoneAuth->number ) )
        {
            return new MessageBag( [ $asterisk->last_result ] );
        }

        $phoneAuth->delete();

        return true;

    }

    private static function callWithCode ( $number, $code )
    {

        $asterisk = Provider::getCurrent()->getAsterisk();

        if ( ! $asterisk || ! $asterisk->originate( $number, $code ) )
        {
            return new MessageBag([ 'Ошибка Астериска' ]);
        }

        return true;

    }

    public static function genCode ()
    {

        $code = '';
        for ( $i = 0; $i < self::$code_length; $i ++ )
        {
            $code .= rand( 0, 8 );
        }

        return $code;

    }

}