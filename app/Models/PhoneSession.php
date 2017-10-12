<?php
declare ( strict_types = 1 );

namespace App\Models;

class PhoneSession extends BaseModel
{

    protected $table = 'phone_sessions';

    public static $name = 'Телефонная сессия';

    public static $rules = [
        'user_id'       => 'required|integer|unique:phone_sessions,user_id,NULL,id,deleted_at,NULL',
        'number'        => 'required|string|min:2'
    ];

    protected $fillable = [ 'user_id', 'number' ];

    public function user ()
    {
        return $this->belongsTo ( 'App\User' );
    }

}