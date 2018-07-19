<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;

class Work extends BaseModel
{

    protected $table = 'works';
    public static $_table = 'works';

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
        'provider_id'       => 'nullable|integer',
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
        'date_end_fact'     => 'nullable|date_format:d.m.Y',
        'time_end_fact'     => 'nullable|date_format:G:i',
        'phone'             => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
    ];

    protected $nullable = [
        'time_end_fact',
        'provider_id',
        'phone',
    ];

    protected $fillable = [
        'provider_id',
        'category_id',
        'management_id',
        'who',
        'reason',
        'text',
        'composition',
        'phone',
		'time_begin',
		'time_end',
        'time_end_fact',
    ];

    public function category ()
    {
        return $this->belongsTo( 'App\Models\Category' );
    }

    public function buildings ()
    {
        return $this->belongsToMany( 'App\Models\Building', 'works_buildings' );
    }

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

    public static function create ( array $attributes = [] )
    {

        if ( ! empty( $attributes['phone'] ) )
        {
            $attributes['phone'] = mb_substr( preg_replace( '/\D/', '', $attributes['phone'] ), -10 );
        }

        $exp = explode( ':', $attributes['time_begin'] );
        $dt_begin = Carbon::parse( $attributes['date_begin'] )->setTime( $exp[0], $exp[1], 0 );

        $exp = explode( ':', $attributes['time_end'] );
        $dt_end = Carbon::parse( $attributes['date_end'] )->setTime( $exp[0], $exp[1], 0 );

        if ( ! empty( $attributes['time_end_fact'] ) )
        {
            $exp = explode( ':', $attributes['time_end_fact'] );
            $dt_end_fact = Carbon::parse( $attributes['date_end_fact'] )->setTime( $exp[0], $exp[1], 0 );
            $attributes['time_end_fact'] = $dt_end_fact->toDateTimeString();
        }
        else
        {
            $attributes['time_end_fact'] = null;
        }

        $attributes['time_begin'] = $dt_begin->toDateTimeString();
        $attributes['time_end'] = $dt_end->toDateTimeString();

        $work = parent::create( $attributes );
        $work->save();

        $res = $work->addLog( 'Добавлена работа на сетях' );
        if ( $res instanceof MessageBag )
        {
            return $res;
        }

        $message = '<em>Добавлена работа на сетях</em>' . PHP_EOL . PHP_EOL;

        foreach ( $work->buildings as $building )
        {
            $message .= '<b>Адрес работы: ' . $building->name . '</b>' . PHP_EOL;
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

        if ( ! empty( $attributes['time_end_fact'] ) )
        {
            $exp = explode( ':', $attributes['time_end_fact'] );
            $dt_end_fact = Carbon::parse( $attributes['date_end_fact'] )->setTime( $exp[0], $exp[1], 0 );
            $attributes['time_end_fact'] = $dt_end_fact->toDateTimeString();
        }
        else
        {
            $attributes['time_end_fact'] = null;
        }

        $attributes['time_begin'] = $dt_begin->toDateTimeString();
        $attributes['time_end'] = $dt_end->toDateTimeString();

        return parent::edit( $attributes );

    }

    public function scopeMine ( $query )
    {
        $query
            ->whereHas( 'buildings', function ( $buildings )
            {
                return $buildings
                    ->whereHas( 'provider', function ( $provider )
                    {
                        return $provider
                            ->mine()
                            ->current();
                    });
            });
        if ( ! \Auth::user()->can( 'supervisor.all_managements' ) )
        {
            $query
                ->whereHas( 'management', function ( $management )
                {
                    return $management
                        ->mine();
                });
        }
        return $query;
    }

    public function scopeCurrent ( $query )
    {
        return $query
            ->where( function ( $q )
            {
                return $q
                    ->whereNull( self::$_table . '.time_end_fact' )
                    ->orWhere( self::$_table . '.time_end_fact', '>=', Carbon::now()->toDateTimeString() );
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
        if ( $this->isExpired() )
        {
            return 'danger';
        }
        else if ( $dt_begin->timestamp > $dt_now->timestamp )
        {
            return 'text-muted';
        }
        else
        {
            return '';
        }
    }

    public function sendTelegram ( $message = null )
    {

        if ( ! \Config::get( 'telegram.active' ) || empty( $message ) || ! $this->management->has_contract ) return;

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

    public function isExpired ()
    {
        if ( ! $this->time_end_fact )
        {
            return Carbon::parse( $this->time_end )->timestamp <= Carbon::now()->timestamp;
        }
        else
        {
            return Carbon::parse( $this->time_end )->timestamp > Carbon::parse( $this->time_end_fact )->timestamp;
        }
    }

    public function canComment ()
    {
        return \Auth::user()->can( 'works.comments_add' );
    }

}
