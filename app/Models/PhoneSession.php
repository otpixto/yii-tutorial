<?php
declare ( strict_types = 1 );

namespace App\Models;

use Illuminate\Support\Facades\Validator;

class PhoneSession extends BaseModel
{

    protected $table = 'phone_sessions';

    public static $name = 'Телефонная сессия';

    public static $rules = [
        'user_id'       => 'required|integer|unique:phone_sessions,user_id,NULL,id,deleted_at,NULL',
        'ext_number'    => 'required|string|min:2|max:4'
    ];

    protected $fillable = [ 'user_id', 'ext_number' ];

    public function user ()
    {
        return $this->belongsTo ( 'App\User' );
    }

    public static function create ( array $attributes = [] )
    {
        $v = Validator::make( $attributes, self::$rules );
        if ( $v->fails() ) return $v->messages();
        $session = new static( $attributes );
        $session->save();
        return $session;
    }

}