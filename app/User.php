<?php

namespace App;

use App\Classes\Asterisk;
use App\Models\Log;
use App\Models\Ticket;
use App\Notifications\MailResetPasswordToken;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\MessageBag;
use Iphome\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    public $availableStatuses = null;

    protected $nullable = [
        'company',
        'phone',
        'middlename',
        'roles',
    ];

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'phone',
        'email',
        'company',
        'password',
    ];

    public static $rules_create = [
        'firstname' => [
            'required',
            'max:255',
        ],
        'middlename' => [
            'nullable',
            'max:255',
        ],
        'lastname' => [
            'required',
            'max:255',
        ],
        'phone' => [
            'nullable',
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
        'company' => [
            'nullable',
            'max:255',
        ],
        'roles' => [
            'nullable',
            'array',
        ],
    ];

    public static $rules_edit = [
        'firstname' => [
            'required',
            'max:255',
        ],
        'middlename' => [
            'nullable',
            'max:255',
        ],
        'lastname' => [
            'required',
            'max:255',
        ],
        'phone' => [
            'nullable',
            'max:18',
            'regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        ],
        'company' => [
            'nullable',
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

    public function managements ()
    {
        return $this->belongsToMany( 'App\Models\Management', 'users_managements' );
    }

    public function regions ()
    {
        return $this->belongsToMany( 'App\Models\Region', 'users_regions' );
    }

    /**
     * Авторизация на телефоне
     */
    public function phoneSession ()
    {
        return $this->hasOne( 'App\Models\PhoneSession' );
    }

    public static function create ( array $attributes = [] )
    {

        $attributes['password'] = bcrypt( $attributes['password'] );
        $attributes['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $attributes['phone'] ) ), -10 );

        $user = new User( $attributes );
        $user->save();

        return $user;

    }

    public function edit ( array $attributes = [] )
    {

        $attributes['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $attributes['phone'] ) ), -10 );

        $this->saveLogs( $attributes );

        $this->fill( $attributes );
        $this->save();

        return $this;

    }

    public function changePass ( array $attributes = [] )
    {

        $this->addLog( 'Пароль изменен' );

        $this->password = bcrypt( $attributes['password'] );
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

    public function getPosition ()
    {
        if ( $this->hasRole( 'control' ) )
        {
            return '<b class="text-info">[Контролирующий]</b>';
        }
        else if ( $this->hasRole( 'operator' ) )
        {
            return '<b class="text-info">[Оператор ЕДС]</b>';
        }
        elseif ( $this->hasRole( 'management' ) && $this->company )
        {
            return '<b class="text-info">[' . $this->company . ']</b>';
        }
        elseif ( $this->hasRole( 'management' ) && $this->managements->count() )
        {
            return '<b class="text-info">[' . $this->managements()->first()->name . ']</b>';
        }
        else
        {
            return '';
        }
    }

    public function getFullName ()
    {
        if ( $this->hasRole( 'control' ) )
        {
            return '<i>[Контролирующий]</i> ' . $this->getName();
        }
        else if ( $this->hasRole( 'operator' ) )
        {
            return '<i>[Оператор ЕДС]</i> ' . $this->getName();
        }
        elseif ( $this->hasRole( 'management' ) && $this->managements->count() )
        {
            return '<i>[' . ( $this->company ?? $this->managements()->first()->name ) . ']</i> ' . $this->getName();
        }
        return '';
    }

    public function getAvailableStatuses ( $with_names = false )
    {
        if ( is_null( $this->availableStatuses ) )
        {
            $perms = $this->getAllPermissions();
            $this->availableStatuses = [];
            foreach ( $perms as $perm )
            {
                if ( str_is( 'tickets.statuses.*', $perm->code ) )
                {
                    $status_code = str_replace( 'tickets.statuses.', '', $perm->code );
                    if ( ! isset( Ticket::$statuses[ $status_code ] ) ) continue;
                    $this->availableStatuses[ $status_code ] = Ticket::$statuses[ $status_code ];
                }
            }
        }
        if ( ! $with_names )
        {
            return array_keys( $this->availableStatuses );
        }
        else
        {
            return $this->availableStatuses;
        }
    }

    public function phoneSessionUnreg ()
    {
        if ( ! $this->phoneSession )
        {
            return new MessageBag( [ 'Телефон пользователя не зарегистрирован' ] );
        }
        $asterisk = new Asterisk();
        if ( ! $asterisk->queueRemove( $this->phoneSession->number ) )
        {
            return new MessageBag( [ $asterisk->last_result ] );
        }
        $this->phoneSession->delete();
        \Cookie::forget( 'phone' );
    }

    public function getPhone ( $html = false )
    {
        $phones = '';
        if ( !empty( $this->phone ) )
        {
            $phone = '+7 (' . mb_substr( $this->phone, 0, 3 ) . ') ' . mb_substr( $this->phone, 3, 3 ) . '-' . mb_substr( $this->phone, 6, 2 ). '-' . mb_substr( $this->phone, 8, 2 );
            if ( $html )
            {
                $phones = '<a href="tel:7' . $this->phone . '" class="inherit">' . $phone . '</a';
            }
            else
            {
                $phones = $phone;
            }
        }
        return $phones;
    }

    public function sendPasswordResetNotification ( $token )
    {
        $this->notify( new MailResetPasswordToken( $token ) );
    }

    public function saveLogs ( array $newValues = [] )
    {
        $oldValues = $this->getAttributes();
        foreach ( $newValues as $field => $val )
        {
            if ( ! isset( $oldValues[ $field ] ) || $oldValues[ $field ] == $val ) continue;
            $log = $this->saveLog( $field, $oldValues[ $field ], $val );
            if ( $log instanceof MessageBag )
            {
                return $log;
            }
        }
    }

    public function saveLog ( $field, $oldValue, $newValue )
    {
        $log = $this->addLog( '"' . $field . '" изменено с "' . $oldValue . '" на "' . $newValue . '"' );
        if ( $log instanceof MessageBag )
        {
            return $log;
        }
    }

    public function addLog ( $text )
    {
        $log = Log::create([
            'model_id'      => $this->id,
            'model_name'    => get_class( $this ),
            'text'          => $text
        ]);
        if ( $log instanceof MessageBag )
        {
            return $log;
        }
        $log->save();
    }

    public function isActive ()
    {
        return $this->admin || $this->active;
    }

}
