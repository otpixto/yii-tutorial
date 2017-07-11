<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Iphome\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'email',
        'phone',
        'password',
    ];

    public static $rules_create = [
        'firstname' => [
            'required',
            'max:255',
        ],
        'middlename' => [
            'required',
            'max:255',
        ],
        'lastname' => [
            'required',
            'max:255',
        ],
        'phone' => [
            'required',
            'max:255',
        ],
        'email' => [
            'required',
            'email',
            'unique:users',
            'max:255',
        ],
        'password' => [
            'required',
            'min: 6',
            'confirmed'
        ],
    ];

    public static $rules_edit = [
        'firstname' => [
            'required',
            'max:255',
        ],
        'middlename' => [
            'required',
            'max:255',
        ],
        'lastname' => [
            'required',
            'max:255',
        ],
        'phone' => [
            'required',
            'max:255',
        ],
    ];

    public static $rules_password = [
        'password' => [
            'required',
            'min: 6',
            'confirmed'
        ],
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static function add ( array $input )
    {

        $rules = self::$rules_create;

        $validator = \Validator::make( $input, $rules );
        if ( $validator->fails() ) return $validator->messages();

        $user = new User( $input );
        $user->save();

        return $user;

    }

    public function edit ( array $input )
    {

        $rules = self::$rules_edit;

        $validator = \Validator::make( $input, $rules );
        if ( $validator->fails() ) return $validator->messages();

        $this->fill( $input );
        $this->save();

        return $this;

    }

    public function changePass ( array $input )
    {

        $rules = self::$rules_password;

        $validator = \Validator::make( $input, $rules );
        if ( $validator->fails() ) return $validator->messages();

        $this->password = bcrypt( $input['password'] );
        $this->save();

        return $this;

    }

    public function getName ()
    {
        $name = $this->firstname . ' ' . $this->lastname;
        return $name;
    }

}
