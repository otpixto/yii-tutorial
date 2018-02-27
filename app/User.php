<?php

namespace App;

use App\Classes\Asterisk;
use App\Jobs\SendEmail;
use App\Models\BaseModel;
use App\Models\Ticket;
use App\Notifications\MailResetPasswordToken;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\MessageBag;
use Iphome\Permission\Traits\HasRoles;
use App\Traits\Authorizable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Notifiable, HasRoles, Authenticatable, Authorizable, CanResetPassword, DispatchesJobs;

    public $availableStatuses = null;

    public static $name = 'Пользователь';

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
        'regions' => [
            'required',
            'array',
        ],
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
        'regions' => [
            'array',
        ],
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

    public function customer ()
    {
        return $this->belongsTo( 'App\Models\Customer', 'phone', 'phone' );
    }

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

    public function openPhoneSession ()
    {
        return $this->hasOne( 'App\Models\PhoneSession' )
            ->notClosed();
    }

    public function phoneSessions ()
    {
        return $this->hasMany( 'App\Models\PhoneSession' );
    }

    public static function create ( array $attributes = [] )
    {

        $attributes[ 'password' ] = bcrypt( $attributes[ 'password' ] );

        if ( ! empty( $attributes[ 'phone' ] ) )
        {
            $attributes[ 'phone' ] = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $attributes[ 'phone' ] ) ), -10 );
            $user = User
                ::where( 'phone', $attributes[ 'phone' ] )
                ->first();
            if ( $user )
            {
                return new MessageBag( [ 'Пользователь с таким номером телефона уже создан' ] );
            }
        }

        $user = parent::create( $attributes );
        $user->save();

        if ( ! empty( $attributes[ 'regions' ] ) )
        {
            $user->regions()->attach( $attributes[ 'regions' ] );
        }

        if ( ! empty( $attributes[ 'roles' ] ) )
        {
            $user->assignRole( $attributes[ 'roles' ] );
        }

        return $user;

    }

    public function edit ( array $attributes = [] )
    {

        if ( ! empty( $attributes[ 'phone' ] ) )
        {
            $attributes[ 'phone' ] = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $attributes[ 'phone' ] ) ), -10 );
        }

        $res = parent::edit( $attributes );

        if ( $res instanceof MessageBag )
        {
            return $res;
        }

        $this->regions()->sync( $attributes[ 'regions' ] ?? [] );

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
        elseif ( $this->company )
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
        $return = $this->getName();
        if ( $this->hasRole( 'control' ) )
        {
            $return = '<i>[Контролирующий]</i> ' . $return;
        }
        else if ( $this->hasRole( 'operator' ) )
        {
            $return = '<i>[Оператор ЕДС]</i> ' . $return;
        }
        else if ( $this->company )
        {
            $return = '<i>[' . $this->company . ']</i> ' . $return;
        }
        else if ( $this->hasRole( 'management' ) && $this->managements->count() )
        {
            $return = '<i>[' . $this->managements()->first()->name . ']</i> ' . $return;
        }
        return $return;
    }

    public function getAvailableStatuses ( $with_names = false, $sort = false )
    {
        if ( \Cache::tags( [ 'dynamic', 'users' ] )->has( 'user.availableStatuses.' . $this->id ) )
        {
            $this->availableStatuses = \Cache::tags( [ 'dynamic', 'users' ] )->get( 'user.availableStatuses.' . $this->id );
        }
        else if ( is_null( $this->availableStatuses ) )
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
            \Cache::tags( [ 'dynamic', 'users' ] )->put( 'user.availableStatuses.' . $this->id, $this->availableStatuses, 60 );
        }
        if ( ! $with_names )
        {
            $res = array_keys( $this->availableStatuses );
        }
        else
        {
            $res = $this->availableStatuses;
        }
        if ( $sort )
        {
            asort( $res );
        }
        return $res;
    }

    public function phoneSessionUnreg ()
    {
        if ( ! $this->openPhoneSession )
        {
            return new MessageBag( [ 'Телефон пользователя не зарегистрирован' ] );
        }
        $asterisk = new Asterisk();
        if ( ! $asterisk->queueRemove( $this->openPhoneSession->number ) )
        {
            return new MessageBag( [ $asterisk->last_result ] );
        }
        $this->openPhoneSession->close();
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

    public function isActive ()
    {
        return $this->admin || $this->active;
    }

    public function scopeActive ( $query )
    {
        return $query
            ->where( 'active', '=', 1 )
            ->orWhere( 'admin', '=', 1 );
    }

    public function sendEmail ( $message, $url = null )
    {
        $this->dispatch( new SendEmail( $this, $message, $url ) );
    }

}
