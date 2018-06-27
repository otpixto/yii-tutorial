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
use App\Interfaces\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends BaseModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Notifiable, HasRoles, Authenticatable, Authorizable, CanResetPassword, DispatchesJobs;

    public $availableStatuses = null;

    public static $_table = 'users';

    public static $name = 'Пользователь';

    protected $nullable = [
        'prefix',
        'phone',
        'middlename',
        'roles',
    ];

    protected $fillable = [
        'active',
        'firstname',
        'middlename',
        'lastname',
        'phone',
        'email',
        'prefix',
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
        'prefix' => [
            'nullable',
            'max:255',
        ],
        'roles' => [
            'nullable',
            'array',
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

        if ( ! isset( $attributes[ 'active' ] ) )
        {
            $attributes[ 'active' ] = 0;
        }

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

    public function getPosition ( $html = false )
    {
        $position = '';
        if ( $this->prefix )
        {
            $position = $this->prefix;
        }
        else if ( $this->hasRole( 'control' ) )
        {
            $position = 'Контролирующий';
        }
        else if ( $this->hasRole( 'operator' ) )
        {
            $position = 'Оператор ЕДС';
        }
        elseif ( $this->hasRole( 'management' ) && $this->managements->count() )
        {
            $position = $this->managements->first()->name ?? '';
        }
        if ( $html )
        {
            return '<b class="text-info">[' . $position . ']</b>';
        }
        else
        {
            return $position;
        }
    }

    public function getFullName ()
    {
        $return = $this->getName();
        if ( $this->prefix )
        {
            $return = '<i>[' . $this->prefix . ']</i> ' . $return;
        }
        else if ( $this->hasRole( 'control' ) )
        {
            $return = '<i>[Контролирующий]</i> ' . $return;
        }
        else if ( $this->hasRole( 'operator' ) )
        {
            $return = '<i>[Оператор ЕДС]</i> ' . $return;
        }
        else if ( $this->hasRole( 'management' ) && $this->managements->count() )
        {
            $return = '<i>[' . $this->managements->first()->name . ']</i> ' . $return;
        }
        return $return;
    }

    public function getAvailableStatuses ( $perm_for, $with_names = false, $sort = false )
    {
        if ( \Cache::tags( 'users' )->has( 'user.availableStatuses.' . $this->id ) )
        {
            $this->availableStatuses = \Cache::tags( 'users' )->get( 'user.availableStatuses.' . $this->id );
        }
        else if ( is_null( $this->availableStatuses ) )
        {
            $res = $this->getAllPermissions();
            $this->availableStatuses = [];
            foreach ( $res as $r )
            {
                if ( preg_match( '/tickets.statuses\.(.*)\.(.*)/i', $r->code, $matches ) )
                {
                    $status_code = $matches[ 1 ];
                    $_perm = $matches[ 2 ];
                    if ( ! isset( Ticket::$statuses[ $status_code ] ) ) continue;
                    $this->availableStatuses[ $_perm ][] = $status_code;
                }
            }
            \Cache::tags( 'users' )->put( 'user.availableStatuses.' . $this->id, $this->availableStatuses, 60 );
        }
        $statuses = $this->availableStatuses[ $perm_for ] ?? [];
        $res = [];
        if ( $with_names )
        {
            foreach ( $statuses as $status_code )
            {
                $res[ $status_code ] = Ticket::$statuses[ $status_code ];
            }
        }
        else
        {
            $res = $statuses;
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
        $number = $this->openPhoneSession->number;
        $this->openPhoneSession->close();
        \Cookie::forget( 'phone' );
        $asterisk = new Asterisk();
        if ( ! $asterisk->queueRemove( $number ) )
        {
            return new MessageBag( [ $asterisk->last_result ] );
        }
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

    public function scopeSearch ( $query, $search )
    {
        return $query
            ->where( function ( $q ) use ( $search )
            {
                $s = '%' . str_replace( ' ', '%', $search ) . '%';
                return $q
                    ->where( User::$_table . '.firstname', 'like', $s )
                    ->orWhere( User::$_table . '.middlename', 'like', $s )
                    ->orWhere( User::$_table . '.lastname', 'like', $s )
                    ->orWhere( User::$_table . '.email', 'like', $s )
                    ->orWhere( User::$_table . '.phone', 'like', $s );
            });
    }

    public function sendEmail ( $message, $url = null )
    {
        $this->dispatch( new SendEmail( $this, $message, $url ) );
    }

}
