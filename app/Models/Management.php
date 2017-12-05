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
        'name'                  => 'required|string|max:255',
        'phone'                 => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        'phone2'                => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        'email'                 => 'nullable|email',
        'site'                  => 'nullable|url',
    ];

    protected $nullable = [
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
        'name',
        'address',
        'phone',
        'phone2',
        'director',
        'schedule',
        'email',
        'site',
        'services',
        'category_id',
    ];

    public function addresses ()
    {
        return $this->belongsToMany( 'App\Models\Address', 'managements_addresses' );
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

    public function scopeMine ( $query )
    {
        if ( ! \Auth::user() ) return false;
        if ( ! \Auth::user()->can( 'supervisor.all_regions' ) )
        {
            $query
                ->whereHas( 'addresses', function ( $q )
                {
                    return $q
                        ->whereHas( 'region', function ( $q2 )
                        {
                            return $q2
                                ->mine();
                        });
                });
        }
        if ( ! \Auth::user()->can( 'supervisor.all_managements' ) )
        {
            $query
                ->whereIn( 'managements.id', \Auth::user()->managements->pluck( 'id' ) );
        }
        return $query;
    }

    public static function create ( array $attributes = [] )
    {
        $attributes['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes['phone'] ), -10 );
        if ( !empty( $attributes['phone2'] ) )
        {
            $attributes['phone2'] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes['phone2'] ), -10 );
        }
        $new = new Management( $attributes );
        $new->has_contract = !empty( $attributes['has_contract'] ) ? 1 : 0;
        $new->save();
        return $new;
    }

    public function edit ( array $attributes = [] )
    {
        $attributes['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes['phone'] ), -10 );
        if ( !empty( $attributes['phone2'] ) )
        {
            $attributes['phone2'] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes['phone2'] ), -10 );
        }
        $res = $this->saveLogs( $attributes );
        if ( $res instanceof MessageBag )
        {
            return $res;
        }
        $this->fill( $attributes );
        $this->has_contract = !empty( $attributes['has_contract'] ) ? 1 : 0;
        $this->save();
        return $this;
    }

    public static function telegramSubscribe ( $telegram_code, $telegram_id )
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
                return new MessageBag([ 'Подписка уже ранее была оформлена' ]);
            }
            else
            {
                $managementSubscription = ManagementSubscription::create([
                    'management_id'     => $management->id,
                    'telegram_id'       => $telegram_id
                ]);
                $managementSubscription->save();
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
                $phones = '<a href="tel:7' . $this->phone . '" class="inherit">' . $phone . '</a';
            }
            else
            {
                $phones = $phone;
            }
        }
        if ( !empty( $this->phone2 ) )
        {
            $phone2 = '+7 (' . mb_substr( $this->phone2, 0, 3 ) . ') ' . mb_substr( $this->phone2, 3, 3 ) . '-' . mb_substr( $this->phone2, 6, 2 ). '-' . mb_substr( $this->phone2, 8, 2 );
            $phones .= '; ';
            if ( $html )
            {
                $phones .= '<a href="tel:7' . $this->phone . '" class="inherit">' . $phone2 . '</a';
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
