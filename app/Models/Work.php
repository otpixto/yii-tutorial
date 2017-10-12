<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;

class Work extends BaseModel
{

    protected $table = 'works';

    public static $name = 'Работа на сетях';

    public static $categories = [
        1 => 'ГВС (горячее водоснабжение)',
        2 => 'ХВС (холодное водоснабжение)',
        3 => 'ЭС (электроснабжение)',
        4 => 'ГС (газоснабжение)',
        5 => 'ТС (теплоснабжение)',
        6 => 'БУ (благоустройство)'
    ];

    public static $rules = [
        'category_id'       => 'required|integer',
        'address_id'        => 'required|array',
        'management_id'     => 'required|integer',
        'comment'           => 'max:255',
        'who'               => 'required|max:255',
        'reason'            => 'required|max:255',
        'date_begin'        => 'required|date_format:d.m.Y',
        'time_begin'        => 'required|date_format:G:i',
        'date_end'          => 'required|date_format:d.m.Y',
        'time_end'          => 'required|date_format:G:i',
        'phone'             => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
    ];

    protected $nullable = [
        'phone',
    ];

    protected $fillable = [
        'category_id',
        'management_id',
        'who',
        'reason',
        'text',
        'composition',
        'phone',
		'time_begin',
		'time_end',
    ];

    public function type ()
    {
        return $this->belongsTo( 'App\Models\Type' );
    }

    public function addresses ()
    {
        return $this->belongsToMany( 'App\Models\Address', 'works_addresses' );
    }

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    public static function create ( array $attributes = [] )
    {

        if ( !empty( $attributes['phone'] ) )
        {
            $attributes['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $attributes['phone'] ) ), -10 );
        }

        $exp = explode( ':', $attributes['time_begin'] );
        $dt_begin = Carbon::parse( $attributes['date_begin'] )->setTime( $exp[0], $exp[1] );

        $exp = explode( ':', $attributes['time_end'] );
        $dt_end = Carbon::parse( $attributes['date_end'] )->setTime( $exp[0], $exp[1] );

        $work = new Work( $attributes );
        $work->author_id = Auth::user()->id;
        $work->time_begin = $dt_begin->toDateTimeString();
        $work->time_end = $dt_end->toDateTimeString();
        $work->save();

        $message = '<em>Добавлена работа на сетях</em>' . PHP_EOL . PHP_EOL;

        foreach ( $work->addresses as $address )
        {
            $message .= '<b>Адрес работы: ' . $address->name . '</b>' . PHP_EOL;
        }
        $message .= 'Категория: ' . $work->getCategory() . PHP_EOL;
        $message .= 'Основание: ' . $work->reason . PHP_EOL;
        $message .= 'Исполнитель работ: ' . $work->management->name . PHP_EOL;
        $message .= 'Кто передал: ' . $work->who . PHP_EOL;
        $message .= 'Состав работ: ' . $work->composition . PHP_EOL . PHP_EOL;

        $message .= 'Начало работ: ' . $work->time_begin . PHP_EOL;
        $message .= 'Окончание работ: ' . $work->time_end . PHP_EOL;

        $message .= PHP_EOL . route( 'works.edit', $work->id ) . PHP_EOL;

        $work->sendTelegram( $message );

        $res = $work->addLog( 'Добавлена работа на сетях' );
        if ( $res instanceof MessageBag )
        {
            return $res;
        }

        return $work;

    }

    public function edit ( array $attributes = [] )
    {

        if ( !empty( $attributes['phone'] ) )
        {
            $attributes['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $attributes['phone'] ) ), -10 );
        }

        $exp = explode( ':', $attributes['time_begin'] );
        $dt_begin = Carbon::parse( $attributes['date_begin'] )->setTime( $exp[0], $exp[1], 0 );

        $exp = explode( ':', $attributes['time_end'] );
        $dt_end = Carbon::parse( $attributes['date_end'] )->setTime( $exp[0], $exp[1], 0 );

        $attributes['time_begin'] = $dt_begin->toDateTimeString();
        $attributes['time_end'] = $dt_end->toDateTimeString();

        $res = $this->saveLogs( $attributes );
        if ( $res instanceof MessageBag )
        {
            return $res;
        }

        $this->fill( $attributes );
        $this->save();

        return $this;

    }

    public function scopeFastSearch ( $query, $search )
    {
        $s = '%' . str_replace( ' ', '%', trim( $search ) ) . '%';
        return $query
            ->where( function ( $q ) use ( $s )
            {
                return $q
                    ->where( 'reason', 'like', $s )
                    ->orWhere( 'who', 'like', $s )
                    ->orWhere( 'composition', 'like', $s )
                    ->orWhereHas( 'address', function ( $q2 ) use ( $s )
                    {
                        return $q2->where( 'name', 'like', $s );
                    })
                    ->orWhereHas( 'management', function ( $q2 ) use ( $s )
                    {
                        return $q2->where( 'name', 'like', $s );
                    })
                    ->orWhereHas( 'type', function ( $q2 ) use ( $s )
                    {
                        return $q2->where( 'name', 'like', $s );
                    });
            });
    }

    public function scopeMine ( $query )
    {
        if ( \Auth::user()->hasRole( 'operator' ) || \Auth::user()->hasRole( 'control' ) )
        {
            return $query;
        }
        $addresses = [];
        foreach ( \Auth::user()->managements as $management )
        {
            foreach ( $management->addresses as $address )
            {
                $addresses[] = $address->id;
            }
        }
        return $query
            ->whereHas( 'addresses', function ( $q ) use ( $addresses )
            {
                return $q
                    ->whereIn( 'address_id', $addresses );
            });
    }

    public function getCategory ()
    {
        return self::$categories[ $this->category_id ] ?? null;
    }

    public function getClass ()
    {
        $dt_now = Carbon::now();
        $dt_begin = Carbon::parse( $this->time_begin );
        $dt_end = Carbon::parse( $this->time_end );
        if ( $dt_begin->timestamp <= $dt_now->timestamp && $dt_now->timestamp <= $dt_end->timestamp )
        {
            return 'warning';
        }
        return 'text-muted';
    }

    public function sendTelegram ( $message = null )
    {

        if ( empty( $message ) || ! $this->management->has_contract ) return;

        foreach ( $this->management->subscriptions as $subscription )
        {
            \Telegram::sendMessage([
                'chat_id'                   => $subscription->telegram_id,
                'text'                      => $message,
                'parse_mode'                => 'html',
                'disable_web_page_preview'  => true
            ]);
        }

    }

}
