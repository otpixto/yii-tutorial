<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\MessageBag;

class TicketManagement extends BaseModel
{

    protected $table = 'tickets_managements';

    private $history = [];
	private $can_comment = null;

    public static $statuses = [
        'transferred'	                    => 'Передано в ЭО',
        'transferred_again'	                => 'Передано в ЭО повторно',
        'accepted'                          => 'Принято к исполнению',
        'assigned'                          => 'Назначен исполнитель',
        'completed_with_act'                => 'Выполнено с актом',
        'completed_without_act'		        => 'Выполнено без акта',
		'closed_with_confirm'		        => 'Закрыто с подтверждением',
        'closed_without_confirm'	        => 'Закрыто без подтверждения',
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
            'waiting',
        ],
        'assigned' => [
            'completed_with_act',
            'completed_without_act',
            'not_verified',
            'waiting',
        ],
        'waiting' => [
            'accepted',
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

        $now = Carbon::now();

        switch ( $this->status_code )
        {

            case 'transferred':
            case 'transferred_again':

                if ( $this->ticket->type->period_acceptance )
                {

                    $status_transferred = $this->statusesHistory->whereIn( 'status_code', [ 'transferred', 'transferred_again' ] )->first();
                    $dt = $status_transferred->created_at;
                    $dt->addMinutes( $this->ticket->type->period_acceptance * 60 );

                    if ( $now->timestamp > $dt->timestamp )
                    {
                        return 'danger';
                    }

                }

                return 'warning';

                break;

            case 'accepted':
            case 'assigned':

                if ( $this->ticket->type->period_execution )
                {

                    $status_accepted = $this->statusesHistory->where( 'status_code', 'accepted' )->first();
                    $dt = $status_accepted->created_at;
                    $dt->addMinutes( $this->ticket->type->period_execution * 60 );

                    if ( $now->timestamp > $dt->timestamp )
                    {
                        return 'danger';
                    }

                }

                return 'success';

                break;

            case 'not_verified':
            case 'cancel':
            case 'no_contract':

                return 'danger';

                break;

        }

        return '';

    }
	
	public function canComment ()
    {
        if ( is_null( $this->can_comment ) )
        {
            if ( \Auth::user()->can( 'tickets.comment' ) && $this->status_code != 'closed_with_confirm' && $this->status_code != 'closed_without_confirm' && $this->status_code != 'cancel' )
            {
                $this->can_comment = true;
            }
            else
            {
                $this->can_comment = false;
            }
        }
        return $this->can_comment;
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

        $ticket = $this->ticket;

        switch ( $this->status_code )
        {

            case 'transferred':

                $message = '<em>Добавлено обращение</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Тип обращения: ' . $ticket->type->name . PHP_EOL;

                $message .= 'Текст проблемы: ' . $ticket->text . PHP_EOL . PHP_EOL;

                $message .= 'ФИО заявителя: ' . $ticket->getName() . PHP_EOL;
                $message .= 'Телефон(ы) заявителя: ' . $ticket->getPhones() . PHP_EOL;

                $message .= PHP_EOL . route( 'tickets.show', $ticket->id ) . PHP_EOL;

                $this->sendTelegram( $message );

                break;

            case 'transferred_again':

                $message = '<em>Обращение передано повторно</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Тип обращения: ' . $ticket->type->name . PHP_EOL;

                $message .= 'Текст проблемы: ' . $ticket->text . PHP_EOL . PHP_EOL;

                $message .= 'ФИО заявителя: ' . $ticket->getName() . PHP_EOL;
                $message .= 'Телефон(ы) заявителя: ' . $ticket->getPhones() . PHP_EOL;

                $message .= PHP_EOL . route( 'tickets.show', $ticket->id ) . PHP_EOL;

                $this->sendTelegram( $message );

                break;

            case 'accepted':

                $res = $this->changeTicketStatus([
					'transferred',
					'transferred_again',
                    'waiting'
				]);
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

                $message = '<em>Изменен статус обращения</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Статус обращения: ' . $this->status_name . PHP_EOL;
                $message .= 'Изменения внес: ' . \Auth::user()->getFullName() . PHP_EOL;

                $message .= PHP_EOL . route( 'tickets.show', $ticket->id ) . PHP_EOL;

                $this->sendTelegram( $message );

                break;
				
            case 'assigned':

                $res = $this->changeTicketStatus([
					'accepted'
				]);
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

                $message = '<em>Назначен исполнитель</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Исполнитель: ' . $this->executor . PHP_EOL;
                $message .= 'Изменения внес: ' . \Auth::user()->getFullName() . PHP_EOL;

                $message .= PHP_EOL . route( 'tickets.show', $ticket->id ) . PHP_EOL;

                $this->sendTelegram( $message );

                break;
				
            case 'completed_with_act':
            case 'completed_without_act':
            case 'not_verified':

                $res = $this->changeTicketStatus([
					'assigned'
				]);
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

                $message = '<em>Изменен статус обращения</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Статус обращения: ' . $this->status_name . PHP_EOL;
                $message .= 'Изменения внес: ' . \Auth::user()->getFullName() . PHP_EOL;

                $message .= PHP_EOL . route( 'tickets.show', $ticket->id ) . PHP_EOL;

                $this->sendTelegram( $message );

                break;

            case 'waiting':

                $this->executor = null;
                $this->save();

                $res = $this->changeTicketStatus([
					'accepted',
					'assigned'
				]);
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

                $message = '<em>Изменен статус обращения</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Статус обращения: ' . $this->status_name . PHP_EOL;
                $message .= 'Изменения внес: ' . \Auth::user()->getFullName() . PHP_EOL;

                $message .= PHP_EOL . route( 'tickets.show', $ticket->id ) . PHP_EOL;

                $this->sendTelegram( $message );

                break;

            case 'closed_with_confirm':
            case 'closed_without_confirm':

                $message = '<em>Обращение закрыто</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Статус обращения: ' . $this->status_name . PHP_EOL;

                if ( $ticket->rate )
                {
                    $message .= 'Оценка: ' . $ticket->rate . PHP_EOL;
                    if ( $ticket->rate_comment )
                    {
                        $message .= 'Комментарий: ' . $ticket->rate_comment . PHP_EOL;
                    }
                }

                $message .= PHP_EOL . route( 'tickets.show', $ticket->id ) . PHP_EOL;

                $this->sendTelegram( $message );

                break;

            case 'cancel':

                $message = '<em>Обращение отменено</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;

                $this->sendTelegram( $message );

                break;

        }

    }

    private function changeTicketStatus ( array $apply_statuses = [] )
    {
        if ( $this->ticket->status_code != $this->status_code && ( count( $apply_statuses ) == 0 || in_array( $this->ticket->status_code, $apply_statuses ) ) )
        {
            $res = $this->ticket->changeStatus( $this->status_code, true );
            if ( $res instanceof MessageBag )
            {
                return $res;
            }
        }
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
