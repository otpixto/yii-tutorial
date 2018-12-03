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

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'time_begin',
        'time_end',
        'time_end_fact',
    ];

    protected $nullable = [
        'time_end_fact',
        'provider_id',
        'phone',
        'type_id',
        'deadline',
        'deadline_unit',
        'reason',
    ];

    protected $fillable = [
        'provider_id',
        'category_id',
        'reason',
        'text',
        'composition',
		'time_begin',
		'time_end',
        'time_end_fact',
        'type_id',
        'deadline',
        'deadline_unit',
    ];

    public static $types = [
        'Авария',
        'Технологический инцидент',
        'Плановое отключение',
    ];

    public static $deadline_units = [
        'часы',
        'дни',
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

    public function executor ()
    {
        return $this->belongsTo( 'App\Models\Executor' );
    }

    public function managements ()
    {
        return $this->belongsToMany( 'App\Models\Management', 'works_managements' );
    }

    public function executors ()
    {
        return $this->belongsToMany( 'App\Models\Executor', 'works_executors' );
    }

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

    public static function create ( array $attributes = [] )
    {

        $exp = explode( ':', $attributes[ 'time_begin' ] );
        $dt_begin = Carbon::parse( $attributes[ 'date_begin' ] )
            ->setTime( $exp[ 0 ], $exp[ 1 ], 0 );

        $exp = explode( ':', $attributes[ 'time_end' ] );
        $dt_end = Carbon::parse( $attributes[ 'date_end' ] )
            ->setTime( $exp[ 0 ], $exp[ 1 ], 0 );

        if ( ! empty( $attributes[ 'time_end_fact' ] ) )
        {
            $exp = explode( ':', $attributes[ 'time_end_fact' ] );
            $dt_end_fact = Carbon::parse( $attributes[ 'date_end_fact' ] )
                ->setTime( $exp[ 0 ], $exp[ 1 ], 0 );
            $attributes[ 'time_end_fact' ] = $dt_end_fact->toDateTimeString();
        } else
        {
            $attributes[ 'time_end_fact' ] = null;
        }

        $attributes[ 'time_begin' ] = $dt_begin->toDateTimeString();
        $attributes[ 'time_end' ] = $dt_end->toDateTimeString();

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
        $message .= 'Категория: ' . $work->category->name . PHP_EOL;
        $message .= 'Основание: ' . $work->reason . PHP_EOL;
        $message .= 'Исполнитель работ: ' . $work->managements->implode( 'name', '; ' ) . PHP_EOL;
        $message .= 'Ответственный: ' . $work->executors->implode( 'name', '; ' ) . PHP_EOL;
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
            ->where( 'author_id', '=', \Auth::user()->id )
            ->orWhere( function ( $q )
            {
                $q
                    ->whereHas( 'buildings', function ( $buildings )
                    {
                        return $buildings
                            ->mine();
                    });
                if ( ! \Auth::user()->can( 'supervisor.all_managements' ) )
                {
                    $q
                        ->whereHas( 'managements', function ( $managements )
                        {
                            return $managements
                                ->mineProvider();
                        });
                }
                return $q;
            });
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
	
	public function scopeOverdue ( $query )
	{
		return $query
			->whereRaw( 'time_end < COALESCE( time_end_fact, CURRENT_TIMESTAMP )' );
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

        if ( ! config( 'telegram.active' ) || empty( $message ) ) return;

        foreach ( $this->managements as $management )
        {
            if ( ! $management->has_contract ) continue;
            foreach ( $management->subscriptions as $subscription )
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

    public function getAddressesGroupBySegment ( $withBuildingType = true )
    {
        $result = [];
        foreach ( $this->buildings as $building )
        {
			$name = $building->number;
			if ( $withBuildingType )
			{
				$name .= ' (' . $building->buildingType->name . ')';
			}
			if ( $building->segment_id )
            {
                if ( ! isset( $result[ $building->segment_id ] ) )
                {
                    $result[ $building->segment_id ] = [
                        $building->getFullName( false ),
                        [
                            $name
                        ]
                    ];
                }
                else
                {
                    $result[ $building->segment_id ][ 1 ][] = $name;
                }
            }
            else
            {
                $result[] = [ $building->name ];
            }
        }
        return $result;
    }

}
