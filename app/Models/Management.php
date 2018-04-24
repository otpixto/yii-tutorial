<?php

namespace App\Models;

use Illuminate\Support\MessageBag;

class Management extends BaseModel
{

    protected $table = 'managements';

    public static $name = 'ЭО';

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

    public static $rules = [
        'region_id'             => 'required|integer',
        'address_id'            => 'nullable|integer',
        'guid'                  => 'nullable|unique:managements,guid|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
        'name'                  => 'required|string|max:255',
        'phone'                 => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        'phone2'                => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        'email'                 => 'nullable|email',
        'site'                  => 'nullable|url',
    ];

    protected $nullable = [
        'address_id',
        'guid',
        'phone',
        'phone2',
        'director',
        'schedule',
        'email',
        'site',
        'services',
        'category_id',
    ];

    protected $fillable = [
        'region_id',
        'address_id',
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
    ];

    public function executors ()
    {
        return $this->hasMany( 'App\Models\Executor' );
    }

    public function addresses ()
    {
        return $this->belongsToMany( 'App\Models\Address', 'managements_addresses' );
    }

    public function address ()
    {
        return $this->belongsTo( 'App\Models\Address' );
    }

    public function types ()
    {
        return $this->belongsToMany( 'App\Models\Type', 'managements_types' );
    }

    public function tickets ()
    {
        return $this->hasMany( 'App\Models\TicketManagement' );
    }

    public function subscriptions ()
    {
        return $this->hasMany( 'App\Models\ManagementSubscription' );
    }

    public function region ()
    {
        return $this->belongsTo( 'App\Models\Region' );
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
            ->where( 'category_id', '=', $category_id );
    }

    public function scopeMine ( $query, ... $flags )
    {
        if ( ! \Auth::user() ) return false;
        $query
            ->where( function ( $q ) use ( $flags )
            {
                if ( ! in_array( self::IGNORE_REGION, $flags ) )
                {
                    $q
                        ->orWhereHas( 'region', function ( $region )
                        {
                            return $region
                                ->mine()
                                ->current();
                        });
                }
                if ( ! in_array( self::IGNORE_ADDRESS, $flags ) )
                {
                    $q
                        ->orWhereHas( 'address', function ( $address )
                        {
                            return $address
                                ->whereHas( 'region', function ( $region )
                                {
                                    return $region
                                        ->mine()
                                        ->current();
                                });
                        });
                }
                return $q;
            });
        if ( ! in_array( self::IGNORE_MANAGEMENT, $flags ) && ! \Auth::user()->can( 'supervisor.all_managements' ) )
        {
            $query
                ->whereIn( 'managements.id', \Auth::user()->managements->pluck( 'id' ) );
        }
        return $query;
    }

    public static function create ( array $attributes = [] )
    {
        if ( ! empty( $attributes[ 'phone' ] ) )
        {
            $attributes[ 'phone' ] = str_replace( '+7', '', $attributes[ 'phone' ] );
            $attributes[ 'phone' ] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes[ 'phone' ] ), -10 );
        }
        if ( ! empty( $attributes[ 'phone2' ] ) )
        {
            $attributes[ 'phone2' ] = str_replace( '+7', '', $attributes[ 'phone2' ] );
            $attributes[ 'phone2' ] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes[ 'phone2' ] ), -10 );
        }
        $new = parent::create( $attributes );
        $new->has_contract = !empty( $attributes['has_contract'] ) ? 1 : 0;
        return $new;
    }

    public function edit ( array $attributes = [] )
    {
        if ( ! empty( $attributes[ 'phone' ] ) )
        {
            $attributes[ 'phone' ] = str_replace( '+7', '', $attributes[ 'phone' ] );
            $attributes[ 'phone' ] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes[ 'phone' ] ), -10 );
        }
        if ( ! empty( $attributes[ 'phone2' ] ) )
        {
            $attributes[ 'phone2' ] = str_replace( '+7', '', $attributes[ 'phone2' ] );
            $attributes[ 'phone2' ] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes[ 'phone2' ] ), -10 );
        }
        $attributes[ 'has_contract' ] = !empty( $attributes[ 'has_contract' ] ) ? 1 : 0;
        return parent::edit( $attributes );
    }

    public static function telegramSubscribe ( $telegram_code, array $attributes = [] )
    {
        $management = self
            ::where( 'telegram_code', '=', $telegram_code )
            ->where( 'has_contract', '=', 1 )
            ->first();
        if ( $management )
        {
            $managementSubscription = $management
                ->subscriptions()
                ->where( 'telegram_id', '=', $attributes[ 'telegram_id' ] )
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
            ::where( 'telegram_code', '=', $telegram_code )
            ->where( 'has_contract', '=', 1 )
            ->first();
        if ( $management )
        {
            $managementSubscription = $management
                ->subscriptions()
                ->where( 'telegram_id', '=', $telegram_id )
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

    public function getCategory ()
    {
        return self::$categories[ $this->category_id ];
    }

}
