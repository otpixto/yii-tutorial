<?php

namespace App\Models;

use Illuminate\Support\MessageBag;

class Management extends BaseModel
{

    protected $table = 'managements';
    public static $_table = 'managements';

    public static $name = 'ЭО';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'contract_begin',
        'contract_end',
    ];

    public static $categories = [
        1 => 'УК',
        2 => 'ЖСК, ТСН (ТСЖ)',
        3 => 'РСО',
        4 => 'Прочие',
    ];

    public static $services = [
        'Электроэнергия',
        'Теплоснабжение',
        'Водоотведение',
        'Холодное водоснабжение',
    ];

    protected $nullable = [
        'provider_id',
        'parent_id',
        'building_id',
        'guid',
        'phone',
        'phone2',
        'director',
        'schedule',
        'email',
        'site',
        'services',
        'category_id',
        'contract_number',
        'contract_begin',
        'contract_end',
        'mosreg_username',
        'mosreg_password',
    ];

    protected $fillable = [
        'provider_id',
        'parent_id',
        'building_id',
        'name',
        'phone',
        'phone2',
        'director',
        'schedule',
        'email',
        'site',
        'services',
        'category_id',
        'has_contract',
        'contract_number',
        'contract_begin',
        'contract_end',
        'mosreg_username',
        'mosreg_password',
    ];

    public function executors ()
    {
        return $this->hasMany( 'App\Models\Executor' );
    }

    public function buildings ()
    {
        return $this->belongsToMany( 'App\Models\Building', 'managements_buildings' );
    }

    public function parent ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    public function building ()
    {
        return $this->belongsTo('App\Models\Building');
    }

    public function types ()
    {
        return $this->belongsToMany( 'App\Models\Type', 'managements_types' );
    }

    public function tickets ()
    {
        return $this->hasMany( 'App\Models\TicketManagement' );
    }

    public function acts ()
    {
        return $this->hasMany( 'App\Models\ManagementAct' );
    }

    public function subscriptions ()
    {
        return $this->hasMany( 'App\Models\ManagementSubscription' );
    }

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

    public function users ()
    {
        return $this->belongsToMany( 'App\User', 'users_managements' );
    }
	
	public function author ()
    {
        return $this->belongsTo( 'App\User' );
    }

    public function scopeCategory ( $query, $category_id )
    {
        return $query
            ->where( self::$_table . '.category_id', '=', $category_id );
    }

    public function scopeMine ( $query, ... $flags )
    {
        if ( ! \Auth::user() ) return false;
		if ( ! in_array( self::IGNORE_PROVIDER, $flags ) )
		{
			$query
				->whereHas( 'provider', function ( $provider )
				{
					return $provider
						->mine()
						->current();
				});
		}
        if ( ! in_array( self::IGNORE_MANAGEMENT, $flags ) && ! \Auth::user()->can( 'supervisor.all_managements' ) )
        {
            $query
                ->whereIn( self::$_table . '.id', \Auth::user()->managements()->pluck( Management::$_table . '.id' ) );
        }
        return $query;
    }

    public static function create ( array $attributes = [] )
    {
        $new = parent::create( $attributes );
        return $new;
    }

    public function edit ( array $attributes = [] )
    {
        $attributes[ 'has_contract' ] = ! empty( $attributes[ 'has_contract' ] ) ? 1 : 0;
        return parent::edit( $attributes );
    }

    public function addExecutor ( $name, $phone = null )
    {
        if ( ! empty( $phone ) )
        {
            $phone = str_replace( '+7', '', $phone );
            $phone = mb_substr( preg_replace( '/[^0-9]/', '', $phone ), -10 );
        }
        return Executor::create([
            'management_id'     => $this->id,
            'name'              => $name,
            'phone'             => $phone,
        ]);
    }

    public static function telegramSubscribe ( $telegram_code, array $attributes = [] )
    {
        $management = self
            ::where( self::$_table . '.telegram_code', '=', $telegram_code )
            ->where( self::$_table . '.has_contract', '=', 1 )
            ->first();
        if ( $management )
        {
            $managementSubscription = $management
                ->subscriptions()
                ->where( ManagementSubscription::$_table . '.telegram_id', '=', $attributes[ 'telegram_id' ] )
                ->first();
            if ( $managementSubscription )
            {
                return new MessageBag([ 'Подписка уже ранее была оформлена' ]);
            }
            else
            {
                $attributes[ 'management_id' ] = $management->id;
                $res = ManagementSubscription::create( $attributes );
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }
                $res->save();
                return true;
            }
        }
        else
        {
            return new MessageBag([ 'Неверный пин-код' ]);
        }
    }

    public static function telegramUnSubscribe ( $telegram_code, $telegram_id )
    {
        $management = self
            ::where( self::$_table . '.telegram_code', '=', $telegram_code )
            ->where( self::$_table . '.has_contract', '=', 1 )
            ->first();
        if ( $management )
        {
            $managementSubscription = $management
                ->subscriptions()
                ->where( ManagementSubscription::$_table . '.telegram_id', '=', $telegram_id )
                ->first();
            if ( $managementSubscription )
            {
                $managementSubscription->delete();
                return true;
            }
            else
            {
                return new MessageBag([ 'Подписка не найдена' ]);
            }
        }
        else
        {
            return new MessageBag([ 'Неверный пин-код' ]);
        }
    }

    public function getPhones ( $html = false )
    {
        $phones = '';
        if ( !empty( $this->phone ) )
        {
            $phone = '+7 (' . mb_substr( $this->phone, 0, 3 ) . ') ' . mb_substr( $this->phone, 3, 3 ) . '-' . mb_substr( $this->phone, 6, 2 ). '-' . mb_substr( $this->phone, 8, 2 );
            if ( $html )
            {
                $phones .= '<a href="tel:7' . $this->phone . '" class="inherit">' . $phone . '</a>';
            }
            else
            {
                $phones .= $phone;
            }
        }
        if ( !empty( $this->phone2 ) )
        {
            $phone2 = '+7 (' . mb_substr( $this->phone2, 0, 3 ) . ') ' . mb_substr( $this->phone2, 3, 3 ) . '-' . mb_substr( $this->phone2, 6, 2 ). '-' . mb_substr( $this->phone2, 8, 2 );
            $phones .= '; ';
            if ( $html )
            {
                $phones .= ' <a href="tel:7' . $this->phone2 . '" class="inherit">' . $phone2 . '</a>';
            }
            else
            {
                $phones .= $phone2;
            }
        }
        return $phones;
    }

    public function getAddress ()
    {
        $addr = '';
        if ( $this->building )
        {
            $addr .= $this->building->name . ' (' . $this->building->buildingType->name . ')';
        }
        return $addr;
    }

    public function getCategory ()
    {
        return self::$categories[ $this->category_id ];
    }

    public function hasMosreg ( Management & $management = null )
    {
        if ( $this->mosreg_id && $this->mosreg_username && $this->mosreg_password )
        {
            $management = $this;
            return true;
        }
        if ( $this->parent && $this->parent->mosreg_id && $this->parent->mosreg_username && $this->parent->mosreg_password )
        {
            $management = $this->parent;
            return true;
        }
        return false;
    }

}
