<?php

namespace App\Models;

use App\Classes\Title;
use App\Jobs\SendStream;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\MessageBag;

class TicketManagement extends BaseModel
{

    use DispatchesJobs;

    protected $table = 'tickets_managements';
    public static $_table = 'tickets_managements';

    public static $name = 'Заявка УО';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'scheduled_begin',
        'scheduled_end',
    ];

    private $history = [];

    private $can_upload_act = null;
    private $can_print_act = null;
    private $can_rate = null;
    private $can_set_executor = null;
    private $can_set_management = null;

    private $availableStatuses = null;

    public static $workflow = [
        'created' => [
            'no_contract',
        ],
        'transferred' => [
            'accepted',
            'rejected',
            'waiting',
        ],
        'transferred_again' => [
            'accepted',
            'rejected',
            'waiting',
        ],
        'accepted' => [
            'waiting',
            'assigned',
            'waiting',
        ],
        'assigned' => [
            'completed_with_act',
            'completed_without_act',
            'not_verified',
            'waiting',
            'in_process',
        ],
        'waiting' => [
            'accepted',
        ],
        'completed_with_act' => [
            'closed_with_confirm',
            'closed_without_confirm',
            'transferred_again',
            'in_process',
        ],
        'completed_without_act' => [
            'closed_with_confirm',
            'closed_without_confirm',
            'transferred_again',
            'in_process',
        ],
        'not_verified' => [
            'closed_with_confirm',
            'closed_without_confirm',
            'transferred_again',
            'in_process',
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
        'executor_id',
        'scheduled_begin',
        'scheduled_end',
        'status_code',
        'status_name',
    ];

    public function services ()
    {
        return $this->hasMany( 'App\Models\TicketManagementService' );
    }

    public function executor ()
    {
        return $this->belongsTo( 'App\Models\Executor' )
            ->withTrashed();
    }

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    public function ticket ()
    {
        return $this->belongsTo( 'App\Models\Ticket' );
    }

    public function act ()
    {
        return $this->belongsTo( 'App\Models\ManagementAct' );
    }

    public function statusesHistory ()
    {
        return $this->hasMany( 'App\Models\StatusHistory', 'model_id' )
            ->where( StatusHistory::$_table . '.model_name', '=', get_class( $this ) );
    }

    public function scopeInProcess ( $query )
    {
        return $query
            ->whereIn( self::$_table . '.status_code', [ 'accepted', 'assigned', 'waiting', 'in_process' ] );
    }

    public function scopeNotProcessed ( $query )
    {
        return $query
            ->whereIn( self::$_table . '.status_code', [ 'transferred', 'transferred_again' ] );
    }

    public function scopeCompleted ( $query )
    {
        return $query
            ->whereIn( self::$_table . '.status_code', [ 'completed_with_act', 'completed_without_act', 'not_verified' ] );
    }

    public function scopeClosed ( $query )
    {
        return $query
            ->whereIn( self::$_table . '.status_code', [ 'closed_with_confirm', 'closed_without_confirm', 'cancel' ] );
    }

    public function scopeMine ( $query, ... $flags )
    {
        $query
			->whereHas( 'ticket', function ( $ticket )
			{
                $ticket
                    ->where( Ticket::$_table . '.author_id', '=', \Auth::user()->id )
                    ->orWhereHas( 'provider', function ( $provider )
                    {
                        return $provider
                            ->mine()
                            ->current();
                    });
                return $ticket;
			});
        if ( ! in_array( self::IGNORE_STATUS, $flags ) && ! \Auth::user()->can( 'supervisor.all_statuses.show' ) )
        {
            $query
                ->whereIn( self::$_table . '.status_code', \Auth::user()->getAvailableStatuses( 'show' ) );
        }
		if ( ! in_array( self::IGNORE_MANAGEMENT, $flags ) && ! \Auth::user()->can( 'supervisor.all_managements' ) )
		{
			$query
				->whereIn( self::$_table . '.management_id', \Auth::user()->managements()->pluck( Management::$_table . '.id' ) );
		}
		return $query;
    }

    public function scopeNotFinaleStatuses ( $query )
    {
        return $query
            ->whereNotIn( self::$_table . '.status_code', Ticket::$final_statuses );
    }

    public function saveServices ( array $services = [] )
    {
        if ( ! count( $services ) )
        {
            $this->services()->delete();
            return true;
        }
        $ids = [];
        foreach ( $services as $service )
        {
            if ( ! empty( $service[ 'id' ] ) )
            {
                $ticketManagementService = TicketManagementService::find( $service[ 'id' ] );
                if ( ! $ticketManagementService )
                {
                    return new MessageBag( [ 'Строка выполненных работ не найдена' ] );
                }
                $res = $ticketManagementService->edit( $service );
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }
            }
            else
            {
                $ticketManagementService = $this->createService( $service );
                if ( $ticketManagementService instanceof MessageBag )
                {
                    return $ticketManagementService;
                }
            }
            $ids[] = $ticketManagementService->id;
        }
        $this->services()->whereNotIn( 'id', $ids )->delete();
        return true;
    }

    public function createService ( array $attributes = [] )
    {
        if ( empty( $attributes[ 'ticket_management_id' ] ) )
        {
            $attributes[ 'ticket_management_id' ] = $this->id;
        }
        $TicketManagementService = TicketManagementService::create( $attributes );
        if ( $TicketManagementService instanceof MessageBag )
        {
            return $TicketManagementService;
        }
        $TicketManagementService->save();
        return $TicketManagementService;
    }

    public function getAvailableStatuses ( $perm_for, $with_names = false, $sort = false )
    {
        if ( is_null( $this->availableStatuses ) )
        {
            $user_statuses = \Auth::user()->getAvailableStatuses( $perm_for );
            $this->availableStatuses = [];
            if ( \Auth::user()->can( 'supervisor.all_statuses.' . $perm_for ) )
            {
                $this->availableStatuses = $user_statuses;
            }
            else if ( \Auth::user()->can( 'tickets.status' ) )
            {
                $workflow = self::$workflow[ $this->status_code ] ?? [];
                foreach ( $workflow as $status_code )
                {
                    if ( in_array( $status_code, $user_statuses ) )
                    {
                        $this->availableStatuses[] = $status_code;
                    }
                }
            }
        }
        $res = [];
        if ( $with_names )
        {
            foreach ( $this->availableStatuses as $status_code )
            {
                $res[ $status_code ] = Ticket::$statuses[ $status_code ];
            }
        }
        else
        {
            $res = $this->availableStatuses;
        }
        if ( $sort )
        {
            asort( $res );
        }
        return $res;
    }

    public function getStatusHistory ( $status_code )
    {
        if ( ! isset( $this->history[ $status_code ] ) )
        {
            $history = $this->statusesHistory()
                ->where( StatusHistory::$_table . '.status_code', '=', $status_code )
                ->orderBy( StatusHistory::$_table . '.id', 'desc' )
                ->first();
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
            case 'rejected':
                return 'danger';
                break;
            default:
                return $this->ticket->getClass();
                break;
        }
    }

    public function getTicketNumber ()
    {
        return $this->ticket_id . '/' . $this->id;
    }

    public function canRate ()
    {
        if ( is_null( $this->can_rate ) )
        {
            if ( \Auth::user()->can( 'tickets.rate' ) && ! $this->rate && in_array( $this->status_code, [ 'completed_with_act', 'completed_without_act', 'closed_with_confirm' ] ) )
            {
                $this->can_rate = true;
            }
            else
            {
                $this->can_rate = false;
            }
        }
        return $this->can_rate;
    }

    public function canSetManagement ()
    {
        if ( is_null( $this->can_set_management ) )
        {
            if ( \Auth::user()->can( 'tickets.management' ) )
            {
                $this->can_set_management = true;
            }
            else
            {
                $this->can_set_management = false;
            }
        }
        return $this->can_set_management;
    }

    public function canSetExecutor ()
    {
        if ( is_null( $this->can_set_executor ) )
        {
            if ( \Auth::user()->can( 'tickets.executor' ) && in_array( $this->status_code, [ 'transferred', 'transferred_again', 'accepted', 'assigned', 'waiting', 'in_process' ] ) )
            {
                $this->can_set_executor = true;
            }
            else
            {
                $this->can_set_executor = false;
            }
        }
        return $this->can_set_executor;
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

    public function isFinalStatus ()
    {
        return in_array( $this->status_code, Ticket::$final_statuses );
    }

    # force - принудительно
    public function changeStatus ( $status_code, $force = false )
    {

        if ( ! isset( Ticket::$statuses[ $status_code ] ) )
        {
            return new MessageBag([ 'Некорректный статус' ]);
        }

        $availableStatuses = array_merge( $this->ticket->getAvailableStatuses( 'edit' ), $this->getAvailableStatuses( 'edit' ) );

        if ( ! $force && ! in_array( $status_code, $availableStatuses ) )
        {
            return new MessageBag([ 'Невозможно сменить статус!' ]);
        }

        $statusHistory = $this
            ->statusesHistory()
            ->orderBy( StatusHistory::$_table . '.id', 'desc' )
            ->first();

        if ( $statusHistory && $statusHistory->status_code == $status_code )
        {
            return false;
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

                $message = '<em>Поступила заявка</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Тип заявки: ' . $ticket->type->name . PHP_EOL . PHP_EOL;

                $message .= 'Текст проблемы: ' . $ticket->text . PHP_EOL . PHP_EOL;

                $message .= 'ФИО заявителя: ' . $ticket->getName() . PHP_EOL;
                $message .= 'Телефон(ы) заявителя: ' . $ticket->getPhones() . PHP_EOL . PHP_EOL;

                $message .= 'Период на принятие заявки в работу, час: ' . $ticket->type->period_acceptance . PHP_EOL;
                $message .= 'Период на исполнение, час: ' . $ticket->type->period_execution . PHP_EOL;
                $message .= 'Сезонность устранения: ' . $ticket->type->season . PHP_EOL . PHP_EOL;

                $message .= 'Платно: ' . ( $ticket->type->is_pay ? 'Да' : 'Нет' ) . PHP_EOL;
                $message .= 'Требуется акт: ' . ( $ticket->type->need_act ? 'Да' : 'Нет' ) . PHP_EOL;

                $message .= PHP_EOL . $this->getUrl() . PHP_EOL;

                $this->sendTelegram( $message, true );

                $this->dispatch( new SendStream( 'create', $this ) );

                break;

            case 'transferred_again':

                $message = '<em>Заявка передана повторно</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Тип заявки: ' . $ticket->type->name . PHP_EOL . PHP_EOL;

                $message .= 'Текст проблемы: ' . $ticket->text . PHP_EOL . PHP_EOL;

                $message .= 'ФИО заявителя: ' . $ticket->getName() . PHP_EOL;
                $message .= 'Телефон(ы) заявителя: ' . $ticket->getPhones() . PHP_EOL . PHP_EOL;

                $message .= 'Период на принятие заявки в работу, час: ' . $ticket->type->period_acceptance . PHP_EOL;
                $message .= 'Период на исполнение, час: ' . $ticket->type->period_execution . PHP_EOL;
                $message .= 'Сезонность устранения: ' . $ticket->type->season . PHP_EOL . PHP_EOL;

                $message .= 'Платно: ' . ( $ticket->type->is_pay ? 'Да' : 'Нет' ) . PHP_EOL;
                $message .= 'Требуется акт: ' . ( $ticket->type->need_act ? 'Да' : 'Нет' ) . PHP_EOL;

                $message .= PHP_EOL . $this->getUrl() . PHP_EOL;

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
                $message .= 'Тип заявки: ' . $ticket->type->name . PHP_EOL;
                $message .= 'Изменения внес: ' . \Auth::user()->getName( true ) . PHP_EOL . PHP_EOL;

                $message .= 'Исполнитель: ' . $this->executor->name . PHP_EOL;

                $message .= PHP_EOL . $this->getUrl() . PHP_EOL;

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

                $this->executor_id = null;
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

                $message = '<em>Заявка закрыта</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Тип заявки: ' . $ticket->type->name . PHP_EOL;
                $message .= 'Изменения внес: ' . \Auth::user()->getName( true ) . PHP_EOL . PHP_EOL;

                $message .= 'Статус: ' . $this->status_name . PHP_EOL;

                if ( $this->rate )
                {
                    $message .= 'Оценка: ' . $this->rate . PHP_EOL;
                    if ( $this->rate_comment )
                    {
                        $message .= 'Комментарий: ' . $this->rate_comment . PHP_EOL;
                    }
                }

                $message .= PHP_EOL . $this->getUrl() . PHP_EOL;

                $this->sendTelegram( $message, true );

                break;

            case 'cancel':

                $message = '<em>Заявка отменена</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Тип заявки: ' . $ticket->type->name . PHP_EOL;
                $message .= 'Изменения внес: ' . \Auth::user()->getName( true ) . PHP_EOL;

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

        $this->dispatch( new SendStream( 'update', $this ) );

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

        $message = '<em>Изменен статус заявки</em>' . PHP_EOL . PHP_EOL;

        $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
        $message .= 'Тип заявки: ' . $ticket->type->name . PHP_EOL;
        $message .= 'Изменения внес: ' . \Auth::user()->getName( true ) . PHP_EOL . PHP_EOL;

        $message .= 'Статус: ' . $this->status_name . PHP_EOL;

        $message .= PHP_EOL . $this->getUrl() . PHP_EOL;

        $this->sendTelegram( $message, true );

    }

    public function sendTelegram ( $message = null, $force = false )
    {

        if ( ! \Config::get( 'telegram.active' ) || empty( $message ) || ! $this->management->has_contract || ( ! $force && in_array( $this->status_code, Ticket::$not_notify ) ) ) return;

        foreach ( $this->management->subscriptions as $subscription )
        {
            $subscription->sendTelegram( $message );
        }

    }

    public function getUrl ( $providerDomain = true )
    {
        if ( $providerDomain )
        {
            if ( ! $this->management || ! $this->management->provider || ! $this->management->provider->domain ) return null;
            $url = \Config::get( 'app.ssl' ) ? 'https://' : 'http://';
            $url .= $this->management->provider->domain;
            $url .= route( 'tickets.show', $this->getTicketNumber(), false );
        }
        else
        {
            $url = route( 'tickets.show', $this->getTicketNumber() );
        }
        return $url;
    }

    public static function getCountByStatus ( $status_code, $force = false )
    {
        $key = 'ticket.status.' . Provider::getSubDomain() . '.' . \Auth::user()->id . '.' . $status_code;
        if ( ! $force && \Cache::tags( [ 'dynamic', 'ticket', 'count' ] )->has( $key ) )
        {
            $count = \Cache::tags( [ 'dynamic', 'ticket', 'count' ] )->get( $key );
        }
        else
        {
            $count = self::mine()->where( 'status_code', '=', $status_code )->count();
            \Cache::tags( [ 'dynamic', 'ticket', 'count' ] )->put( $key, $count, \Config::get( 'cache.time' ) );
        }
        return $count;
    }

}
