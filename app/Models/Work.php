<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Work extends BaseModel
{

    protected $table = 'works';

    public static $rules = [
        'type_id'           => 'required|integer',
        'address_id'        => 'required|integer',
        'management_id'     => 'required|integer',
        'comment'           => 'max:255',
        'who'               => 'required|max:255',
        'reason'            => 'required|max:255',
        'date_begin'        => 'required|date_format:d.m.Y',
        'time_begin'        => 'required|date_format:G:i',
        'date_end'          => 'required|date_format:d.m.Y',
        'time_end'          => 'required|date_format:G:i',
    ];

    protected $fillable = [
        'type_id',
        'address_id',
        'management_id',
        'who',
        'reason',
        'text',
        'composition',
    ];

    public function type ()
    {
        return $this->belongsTo( 'App\Models\Type' );
    }

    public function address ()
    {
        return $this->belongsTo( 'App\Models\Address' );
    }

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    public function author ()
    {
        return $this->belongsTo( 'App\User' );
    }

    public static function create ( array $attributes = [] )
    {

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

        $message .= '<b>Адрес работы: ' . $work->getAddress() . '</b>' . PHP_EOL;
        $message .= 'Тип работ: ' . $work->type->name . PHP_EOL;
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

        $exp = explode( ':', $attributes['time_begin'] );
        $dt_begin = Carbon::parse( $attributes['date_begin'] )->setTime( $exp[0], $exp[1] );

        $exp = explode( ':', $attributes['time_end'] );
        $dt_end = Carbon::parse( $attributes['date_end'] )->setTime( $exp[0], $exp[1] );

        $this->fill( $attributes );
        $this->time_begin = $dt_begin->toDateTimeString();
        $this->time_end = $dt_end->toDateTimeString();
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
        return $query
            ->where( function ( $q )
            {
                if ( ! \Auth::user()->hasRole( 'operator' ) )
                {
                    return $q
                        ->whereIn( 'management_id', \Auth::user()->managements->pluck( 'id' ) );
                }

            });
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

    public function getAddress ()
    {
        $addr = '';
        if ( $this->address )
        {
            $addr .= $this->address->name;
        }
        return $addr;
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
