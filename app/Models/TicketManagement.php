<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\MessageBag;
use Telegram\Bot\Exceptions\TelegramResponseException;

class TicketManagement extends BaseModel
{

    protected $table = 'tickets_managements';

    public static $name = 'Заявка УО';

    private $history = [];

    private $can_edit = null;
	private $can_comment = null;
    private $can_upload_act = null;
    private $can_print_act = null;

    private $availableStatuses = null;

    public static $workflow = [
        'transferred' => [
            'accepted',
            'rejected',
        ],
        'transferred_again' => [
            'accepted',
            'rejected',
        ],
        'accepted' => [
            'waiting',
            'assigned',
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
        'completed_with_act' => [
            'closed_with_confirm',
            'closed_without_confirm',
            'transferred_again',
        ],
        'completed_without_act' => [
            'closed_with_confirm',
            'closed_without_confirm',
            'transferred_again',
        ],
        'not_verified' => [
            'closed_with_confirm',
            'closed_without_confirm',
            'transferred_again',
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

    public function statusesHistory ()
    {
        return $this->hasMany( 'App\Models\StatusHistory', 'model_id' )
            ->where( 'model_name', '=', get_class( $this ) );
    }

    public function scopeMine ( $query, $ignoreStatuses = false )
    {
        $query
			->whereHas( 'ticket', function ( $q ) use ( $ignoreStatuses )
			{
				return $q
					->mine( $ignoreStatuses );
			});
		if ( ! \Auth::user()->can( 'supervisor.all_managements' ) )
		{
			$query
				->whereIn( 'management_id', \Auth::user()->managements->pluck( 'id' ) );
		}
		return $query;
    }

    public function scopeNotFinaleStatuses ( $query )
    {
        return $query
            ->whereNotIn( 'status_code', Ticket::$final_statuses );
    }

    public function getAvailableStatuses ()
    {
        if ( is_null( $this->availableStatuses ) )
        {
            $user_statuses = \Auth::user()->getAvailableStatuses();
            $this->availableStatuses = [];
            if ( \Auth::user()->can( 'tickets.status' ) )
            {
                $workflow = self::$workflow[ $this->status_code ] ?? [];
                foreach ( $workflow as $status_code )
                {
                    if ( in_array( $status_code, $user_statuses ) )
                    {
                        $this->availableStatuses[ $status_code ] = Ticket::$statuses[ $status_code ];
                    }
                }
            }
        }
        return $this->availableStatuses;
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
                    if ( $status_transferred )
                    {
                        $dt = $status_transferred->created_at;
                        $dt->addMinutes( $this->ticket->type->period_acceptance * 60 );
                        if ( $now->timestamp > $dt->timestamp )
                        {
                            return 'danger';
                        }
                    }

                }

                return 'warning';

                break;

            case 'accepted':
            case 'assigned':

                /*if ( $this->ticket->type->period_execution )
                {

                    $status_accepted = $this->statusesHistory->where( 'status_code', 'accepted' )->first();
                    if ( $status_accepted )
                    {
                        $dt = $status_accepted->created_at;
                        $dt->addMinutes( $this->ticket->type->period_execution * 60 );
                        if ( $now->timestamp > $dt->timestamp )
                        {
                            return 'danger';
                        }
                    }

                }*/

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

    public function getTicketNumber ()
    {
        return $this->ticket_id . '/' . $this->id;
    }
	
	public function canComment ()
    {
        if ( is_null( $this->can_comment ) )
        {
            if ( \Auth::user()->can( 'tickets.comment_add' ) && $this->management->has_contract )
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

    public function canPrintAct ()
    {
        if ( is_null( $this->can_print_act ) )
        {
            if ( \Auth::user()->can( 'tickets.act' ) && $this->management->has_contract && $this->status_code )
            {
                $this->can_print_act = true;
            }
            else
            {
                $this->can_print_act = false;
            }
        }
        return $this->can_print_act;
    }

    public function canUploadAct ()
    {
        if ( is_null( $this->can_upload_act ) )
        {
            if ( \Auth::user()->can( 'tickets.files' ) && $this->management->has_contract )
            {
                $this->can_upload_act = true;
            }
            else
            {
                $this->can_upload_act = false;
            }
        }
        return $this->can_upload_act;
    }

    # force - принудительно
    public function changeStatus ( $status_code, $force = false )
    {

        if ( ! isset( Ticket::$statuses[ $status_code ] ) )
        {
            return new MessageBag([ 'Некорректный статус' ]);
        }

        if ( ! $force && ! in_array( $status_code, self::$workflow[ $this->status_code ] ?? [] ) )
        {
            return new MessageBag([ 'Невозможно сменить статус!' ]);
        }

        \DB::beginTransaction();

        $log = $this->addLog( 'Статус изменен с "' . $this->status_name . '" на "' . Ticket::$statuses[ $status_code ] . '"' );
        if ( $log instanceof MessageBag )
        {
            return redirect()->back()
                ->withErrors( $log );
        }

        $this->status_code = $status_code;
        $this->status_name = Ticket::$statuses[ $status_code ];
        $this->save();

        $statusHistory = StatusHistory::create([
            'model_id'          => $this->id,
            'model_name'        => get_class( $this ),
            'status_code'       => $status_code,
            'status_name'       => Ticket::$statuses[ $status_code ],
        ]);

        if ( $statusHistory instanceof MessageBag )
        {
            return $statusHistory;
        }

        $statusHistory->save();

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
                $message .= 'Тип обращения: ' . $ticket->type->name . PHP_EOL . PHP_EOL;

                $message .= 'Текст проблемы: ' . $ticket->text . PHP_EOL . PHP_EOL;

                $message .= 'ФИО заявителя: ' . $ticket->getName() . PHP_EOL;
                $message .= 'Телефон(ы) заявителя: ' . $ticket->getPhones() . PHP_EOL;

                $message .= PHP_EOL . route( 'tickets.show', $this->getTicketNumber() ) . PHP_EOL;

                $this->sendTelegram( $message, true );

                break;

            case 'transferred_again':

                $message = '<em>Обращение передано повторно</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Тип обращения: ' . $ticket->type->name . PHP_EOL . PHP_EOL;

                $message .= 'Текст проблемы: ' . $ticket->text . PHP_EOL . PHP_EOL;

                $message .= 'ФИО заявителя: ' . $ticket->getName() . PHP_EOL;
                $message .= 'Телефон(ы) заявителя: ' . $ticket->getPhones() . PHP_EOL;

                $message .= PHP_EOL . route( 'tickets.show', $this->getTicketNumber() ) . PHP_EOL;

                $this->sendTelegram( $message, true );

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

                $this->sendTelegramChangeStatus();

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
                $message .= 'Тип обращения: ' . $ticket->type->name . PHP_EOL;
                $message .= 'Исполнитель: ' . $this->executor . PHP_EOL;
                $message .= 'Изменения внес: ' . \Auth::user()->getFullName() . PHP_EOL;

                $message .= PHP_EOL . route( 'tickets.show', $this->getTicketNumber() ) . PHP_EOL;

                $this->sendTelegram( $message, true );

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

                $this->sendTelegramChangeStatus();

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

                $this->sendTelegramChangeStatus();

                break;

            case 'closed_with_confirm':
            case 'closed_without_confirm':

                $res = $this->changeTicketStatus([
                    'completed_with_act',
                    'completed_without_act',
                    'not_verified'
                ]);
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

                $message = '<em>Обращение закрыто</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Тип обращения: ' . $ticket->type->name . PHP_EOL;
                $message .= 'Статус обращения: ' . $this->status_name . PHP_EOL;

                if ( $ticket->rate )
                {
                    $message .= 'Оценка: ' . $ticket->rate . PHP_EOL;
                    if ( $ticket->rate_comment )
                    {
                        $message .= 'Комментарий: ' . $ticket->rate_comment . PHP_EOL;
                    }
                }

                $message .= PHP_EOL . route( 'tickets.show', $this->getTicketNumber() ) . PHP_EOL;

                $this->sendTelegram( $message, true );

                break;

            case 'cancel':

                $message = '<em>Обращение отменено</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Тип обращения: ' . $ticket->type->name . PHP_EOL;

                $this->sendTelegram( $message, true );

                break;

            case 'rejected':

                $this->sendTelegramChangeStatus();

                break;

        }

        if ( $this->ticket->status_code != 'draft' && $this->ticket->managements->count() == 1 )
        {
            $res = $this->changeTicketStatus();
            if ( $res instanceof MessageBag )
            {
                return $res;
            }
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

    private function sendTelegramChangeStatus ()
    {

        $ticket = $this->ticket;

        $message = '<em>Изменен статус обращения</em>' . PHP_EOL . PHP_EOL;

        $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
        $message .= 'Тип обращения: ' . $ticket->type->name . PHP_EOL;
        $message .= 'Статус обращения: ' . $this->status_name . PHP_EOL;
        $message .= 'Изменения внес: ' . \Auth::user()->getFullName() . PHP_EOL;

        $message .= PHP_EOL . route( 'tickets.show', $this->getTicketNumber() ) . PHP_EOL;

        $this->sendTelegram( $message, true );

    }

    public function sendTelegram ( $message = null, $force = false )
    {

        if ( ! \Config::get( 'telegram.active' ) || empty( $message ) || ! $this->management->has_contract || ( ! $force && in_array( $this->status_code, Ticket::$not_notify ) ) ) return;

        foreach ( $this->management->subscriptions as $subscription )
        {
            try
            {
                $response = \Telegram::sendMessage([
                    'chat_id'                   => $subscription->telegram_id,
                    'text'                      => $message,
                    'parse_mode'                => 'html',
                    'disable_web_page_preview'  => true
                ]);
                $chat = $response->getChat();
                if ( $chat )
                {
                    $attributes = [
                        'first_name' => $chat->getFirstName() ?? null,
                        'last_name' => $chat->getLastName() ?? null,
                        'username' => $chat->getUsername()
                    ];
                    $subscription->edit( $attributes );
                }
            }
            catch ( TelegramResponseException $e )
            {
                $errorData = $e->getResponseData();
                if ( $errorData['ok'] === false )
                {
                    $subscription->addLog( 'Подписка удалена по причине "' . $errorData['description'] . '"' );
                    $subscription->delete();
                }
            }
        }

    }

}
