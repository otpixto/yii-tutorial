<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\MessageBag;

class TicketManagement extends BaseModel
{

    protected $table = 'tickets_managements';

    private $history = [];

    public static $statuses = [
        null                                => 'Статус не назначен',
        'transferred'	                    => 'Передано в ЭО',
        'transferred_again'	                => 'Передано в ЭО повторно',
        'accepted'                          => 'Принято к исполнению',
        'assigned'                          => 'Назначен исполнитель',
        'completed_with_act'                => 'Выполнено с актом',
        'completed_without_act'		        => 'Выполнено без акта',
        'not_verified'		                => 'Проблема не подтверждена',
        'waiting'	                        => 'Отложено',
        'no_contract'	                    => 'Отказ (нет договора с ЭО)',
		'cancel'				            => 'Отмена',
    ];

    public static $workflow = [
        'transferred' => [
            'accepted',
        ],
        'transferred_again' => [
            'accepted',
        ],
        'accepted' => [
            'assigned',
            'waiting'
        ],
        'assigned' => [
            'completed_with_act',
            'completed_without_act',
            'not_verified',
            'waiting'
        ],
        'waiting' => [
            'assigned',
        ],
    ];

    protected $nullable = [
        'status_code',
        'status_name'
    ];

    public static $rules = [
        'ticket_id'             => 'required|integer',
        'management_id'         => 'required|integer',
        'status_code'           => 'nullable|max:191',
        'status_name'           => 'nullable|max:191',
    ];

    protected $fillable = [
        'ticket_id',
        'management_id',
        'status_code',
        'status_name',
    ];

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    public function ticket ()
    {
        return $this->belongsTo( 'App\Models\Ticket' );
    }

    public function comments ()
    {
        return $this->hasMany( 'App\Models\Comment', 'model_id' )
            ->where( 'model_name', '=', get_class( $this ) );
    }

    public function statusesHistory ()
    {
        return $this->hasMany( 'App\Models\StatusHistory', 'model_id' )
            ->where( 'model_name', '=', get_class( $this ) );
    }

    public function files ()
    {
        return $this->hasMany( 'App\Models\File', 'model_id' )
            ->where( 'model_name', '=', get_class( $this ) );
    }

    public function tags ()
    {
        return $this->hasMany( 'App\Models\Tag', 'model_id' )
            ->where( 'model_name', '=', get_class( $this ) );
    }

    public function scopeMine ( $query )
    {
        return $query
            ->whereIn( 'status_code', \Auth::user()->getAvailableStatuses() );
    }

    public static function create ( array $attributes = [] )
    {

        $ticketManagement = new TicketManagement( $attributes );
        $ticketManagement->save();
        return $ticketManagement;

    }

    public function getAvailableStatuses ()
    {
        $workflow = self::$workflow[ $this->status_code ] ?? [];
        $statuses = [];
        foreach ( $workflow as $status_code )
        {
            $statuses[ $status_code ] = self::$statuses[ $status_code ];
        }
        return $statuses;
    }

    public function addComment ( $text )
    {

        $comment = Comment::create([
            'model_id'     	=> $this->id,
            'model_name'	=> get_class( $this ),
            'text'          => $text
        ]);

        return $comment;

    }

    public function addTag ( $text )
    {

        $tag = Tag::create([
            'model_id'     	=> $this->id,
            'model_name'	=> get_class( $this ),
            'text'          => $text
        ]);

        return $tag;

    }

    public function getStatusHistory ( $status_code )
    {
        if ( ! isset( $this->history[ $status_code ] ) )
        {
            $history = $this->statusesHistory()->where( 'status_code', '=', $status_code )->orderBy( 'id', 'desc' )->first();
            if ( ! $history )
            {
                return null;
            }
            $this->history[ $status_code ] = $history;
        }
        return $this->history[ $status_code ];
    }

    public function getClass ()
    {

        switch ( $this->status_code )
        {

            case 'not_verified':
            case 'cancel':
            case 'no_contract':
                return 'danger';
                break;

            case 'accepted':
            case 'assigned':
                return 'success';
                break;

            case 'transferred_again':
                return 'warning';
                break;

        }

        return '';

    }

    # force - принудительно
    public function changeStatus ( $status_code, $force = false )
    {

        if ( ! isset( self::$statuses[ $status_code ] ) )
        {
            return new MessageBag([ 'Некорректный статус' ]);
        }

        if ( ! $force && ! in_array( $status_code, self::$workflow[ $this->status_code ] ?? [] ) )
        {
            return new MessageBag([ 'Невозможно сменить статус!' ]);
        }

        \DB::beginTransaction();

        $this->status_code = $status_code;
        $this->status_name = self::$statuses[ $status_code ];
        $this->save();

        StatusHistory::create([
            'model_id'          => $this->id,
            'model_name'        => get_class( $this ),
            'status_code'       => $status_code,
            'status_name'       => self::$statuses[ $status_code ],
        ]);

        $res = $this->processStatus();
        if ( $res instanceof MessageBag )
        {
            return $res;
        }

        \DB::commit();

    }

    public function processStatus ()
    {

        switch ( $this->status_code )
        {

            case 'accepted':
            case 'assigned':
            case 'completed_with_act':
            case 'completed_without_act':
            case 'not_verified':
            case 'waiting':

                if ( $this->ticket->status_code != $this->status_code )
                {
                    $res = $this->ticket->changeStatus( $this->status_code, true );
                    if ( $res instanceof MessageBag )
                    {
                        return $res;
                    }
                }

                break;

        }

    }

}
