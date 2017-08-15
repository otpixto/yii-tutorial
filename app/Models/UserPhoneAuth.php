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
    protected $primaryKey = 'id';

    private static $code_length = 4; // длина кода авторизации
    public static $timeout = 30; // таймаут авторизации

    protected $fillable = [ 'user_id', 'ext_number', 'code' ];
    private static $asterisk;

    public function __construct ( array $attributes = [] )
    {
        parent::__construct( $attributes );
        $this->where( 'created_at', '<=', Carbon::now()->subSeconds( self::$timeout )->toDateTimeString() )->delete();
        self::$asterisk = new Asterisk();
    }

    public function user ()
    {
        return $this->hasOne( 'App\User' );
    }

    public static function create ( array $attributes = [] )
    {

        $rules = [
            'ext_number' => 'required|min:2|max:4'
        ];

        $v = Validator::make ( $attributes, $rules );
        if ( $v->fails () ) return $v->messages ();

        if ( \Auth::user()->phoneSession )
        {
            return new MessageBag([ 'Пользовательский телефон уже зарегистрирован' ]);
        }

        $r = UserPhoneAuth::find( \Auth::user ()->id );
        if ( $r )
        {
            $r->delete();
        }

        $r = UserPhoneAuth::where( 'ext_number', '=', $attributes['ext_number'] )->first();
        if ( $r )
        {
            $r->delete();
        }

        $phoneAuth = new UserPhoneAuth( $attributes );
        $phoneAuth->user_id = \Auth::user()->id;
        $phoneAuth->code = self::genCode();
        $phoneAuth->save();

        return self::callWithCode( $phoneAuth->ext_number, $phoneAuth->code );

    }

    public static function confirm ( array $attributes = [] )
    {

        $rules = [
            'ext_number'    => 'required|min:2|max:4',
            'code'          => 'required|digits:' . self::$code_length
        ];

        $v = Validator::make( $attributes, $rules );
        if ( $v->fails() ) return $v->messages();

        $phoneAuth = UserPhoneAuth
            ::where( 'ext_number', '=', $attributes['ext_number'] )
            ->where( 'code', '=', $attributes['code'] )
            ->first();

        if ( ! $phoneAuth )
        {
            return new MessageBag( [ 'Неверный код' ] );
        }

        if ( ! self::$asterisk->queueAdd( $phoneAuth->ext_number ) )
        {
            return new MessageBag( [ self::$asterisk->last_result ] );
        }

        $phoneAuth->delete();

        return true;

    }

    private static function callWithCode ( $ext_number, $code )
    {

        if ( !self::$asterisk || !self::$asterisk->originate( $ext_number, $code ) )
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
            $code .= rand( 0, 9 );
        }

        return $code;

    }

}