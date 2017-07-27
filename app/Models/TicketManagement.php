<?php

namespace App\Models;

use Illuminate\Support\MessageBag;

class TicketManagement extends BaseModel
{

    protected $table = 'tickets_managements';

    public static $statuses = [
        null                                => 'Статус не назначен',
        'transferred_management'	        => 'Передано Исполнителю',
        'transferred_management_again'	    => 'Передано Исполнителю Повторно',
        'accepted'                          => 'Принята к исполнению',
        'assigned'                          => 'Назначен ответственный',
        'completed_with_act'                => 'Выполнено с актом',
        'completed_without_act'		        => 'Выполнено без акта',
        'not_verified'		                => 'Проблема не подтверждена',
        'waiting'	                        => 'Отложено',
        'no_contract'	                    => 'Отказ (отсутствует договор)',
    ];

    public static $workflow = [
        null => [
            'transferred_management',
            'no_contract',
        ],
        'transferred_management' => [
            'accepted',
            'assigned',
            'waiting'
        ],
        'transferred_management_again' => [
            'accepted',
            'assigned',
            'waiting'
        ],
        'accepted' => [
            'assigned',
            'completed_with_act',
            'completed_without_act',
            'not_verified',
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

    public static function create ( array $attributes = [] )
    {

        $ticketManagement = new TicketManagement( $attributes );
        $ticketManagement->save();
        return $ticketManagement;

    }

    public function getAvailableStatuses ()
    {
        $workflow = self::$workflow[ $this->status_code ] ?? [];
        $statuses = [
            null => ' -- выберите из списка -- '
        ];
        foreach ( $workflow as $status_code )
        {
            $statuses[ $status_code ] = self::$statuses[ $status_code ];
        }
        return count( $statuses ) > 1 ? $statuses : [];
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

                $ticket = $this->ticket;
                if ( $ticket->status_code != 'accepted_management' && ( $ticket->managements->count() == 1 || $ticket->managements()->whereNotIn( 'status_code', [ 'accepted', 'assigned' ] )->count() == 0 ) )
                {
                    $res = $ticket->changeStatus( 'accepted_management' );
                    if ( $res instanceof MessageBag )
                    {
                        return $res;
                    }
                }

                break;

            case 'completed_with_act':

                $ticket = $this->ticket;
                if ( $ticket->managements->count() == 1 || $ticket->managements()->where( 'status_code', '!=', 'completed_with_act' )->count() == 0 )
                {
                    $res = $ticket->changeStatus( 'completed_with_act' );
                    if ( $res instanceof MessageBag )
                    {
                        return $res;
                    }
                }

                break;

            case 'completed_without_act':

                $ticket = $this->ticket;
                if ( $ticket->managements->count() == 1 || $ticket->managements()->where( 'status_code', '!=', 'completed_without_act' )->count() == 0 )
                {
                    $res = $ticket->changeStatus( 'completed_without_act' );
                    if ( $res instanceof MessageBag )
                    {
                        return $res;
                    }
                }

                break;

            case 'not_verified':

                $ticket = $this->ticket;
                if ( $ticket->managements->count() == 1 || $ticket->managements()->where( 'status_code', '!=', 'not_verified' )->count() == 0 )
                {
                    $res = $ticket->changeStatus( 'not_verified' );
                    if ( $res instanceof MessageBag )
                    {
                        return $res;
                    }
                }

                break;

        }

    }

}
