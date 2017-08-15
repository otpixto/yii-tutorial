<?php

namespace App;

use App\Classes\Asterisk;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\MessageBag;
use Iphome\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    public $availableStatuses = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'phone',
        'email',
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
            'max:18',
            'regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
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

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    /**
     * Авторизация на телефоне
     */
    public function phoneSession ()
    {
        return $this->hasOne( 'App\Models\PhoneSession' );
    }

    public static function add ( array $input )
    {

        $rules = self::$rules_create;

        $validator = \Validator::make( $input, $rules );
        if ( $validator->fails() ) return $validator->messages();

        $input['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', $input['phone'] ), -10 );

        $user = new User( $input );
        $user->save();

        return $user;

    }

    public function edit ( array $input )
    {

        $rules = self::$rules_edit;

        $validator = \Validator::make( $input, $rules );
        if ( $validator->fails() ) return $validator->messages();

        $input['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', $input['phone'] ), -10 );

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
        $name = $this->lastname . ' ' . $this->firstname . ' ' . $this->middlename;
        return $name;
    }

    public function getShortName ()
    {
        $name = $this->lastname . ' ' . mb_substr( $this->firstname, 0, 1 ) . '. ' . mb_substr( $this->middlename, 0, 1 ) . '.';
        return $name;
    }

    public function getAvailableStatuses ( $flush = false )
    {

        if ( $flush || is_null( $this->availableStatuses ) )
        {
            $perms = $this->getAllPermissions();
            $statuses = [];
            foreach ( $perms as $perm )
            {
                if ( str_is( 'tickets.statuses.*', $perm->code ) )
                {
                    $statuses[] = str_replace( 'tickets.statuses.', '', $perm->code );
                }
            }
            $this->availableStatuses = $statuses;
        }

        return $this->availableStatuses;

    }

    public function phoneSessionUnreg ()
    {
        if ( ! $this->phoneSession )
        {
            return new MessageBag( [ 'Телефон пользователя не зарегистрирован' ] );
        }
        $asterisk = new Asterisk();
        if ( ! $asterisk->queueRemove( $this->phoneSession->ext_number ) )
        {
            return new MessageBag( [ $asterisk->last_result ] );
        }
        $this->phoneSession->delete();
    }

}
