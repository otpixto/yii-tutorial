<?php

namespace App\Models;

use Illuminate\Support\MessageBag;

class Management extends BaseModel
{

    protected $table = 'managements';

    public static $categories = [
        'УК',
        'ЖСК, ТСН (ТСЖ)',
        'РСО',
        'Прочие',
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
        'category',
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
        'category',
    ];

    public function addresses ()
    {
        return $this->belongsToMany( 'App\Models\Address', 'addresses_managements' )
            ->withPivot( [ 'type_id' ] );
    }

    public function tickets ()
    {
        return $this->hasMany( 'App\Models\TicketManagement' );
    }

    public function subscriptions ()
    {
        return $this->hasMany( 'App\Models\ManagementSubscription' );
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

}
