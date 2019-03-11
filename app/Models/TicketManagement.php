<?php

namespace App\Models;

use App\Classes\Mosreg;
use App\Jobs\SendStream;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
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
            'transferred',
        ],
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
        'rejected' => [
            'created',
        ],
        'assigned' => [
            'in_process',
        ],
        'waiting' => [
            'accepted',
        ],
        'in_process' => [
            'waiting',
            'not_verified',
            'completed_with_act',
            'completed_without_act'
        ],
        'completed_with_act' => [
            'confirmation_operator',
        ],
        'completed_without_act' => [
            'confirmation_operator',
        ],
        'not_verified' => [
            'confirmation_operator',
        ],
		'from_lk' => [
            'created',
            'cancel',
        ],
        'from_dobrodel' => [
            'created',
            'cancel',
        ],
		'confirmation_operator' => [
			'confirmation_client',
			'transferred_again',
		],
		'confirmation_client' => [
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
        'executor_id',
        'scheduled_begin',
        'scheduled_end',
        'status_code',
        'status_name',
    ];

    public function services ()
    {
        return $this->hasMany( TicketManagementService::class );
    }

    public function executor ()
    {
        return $this->belongsTo( Executor::class )
            ->withTrashed();
    }

    public function management ()
    {
        return $this->belongsTo( Management::class );
    }

    public function ticket ()
    {
        return $this->belongsTo( Ticket::class );
    }

    public function act ()
    {
        return $this->belongsTo( ManagementAct::class );
    }

    public function statusesHistory ()
    {
        return $this->hasMany( StatusHistory::class, 'model_id' )
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
			->whereHas( 'ticket', function ( $ticket ) use ( $flags )
			{
                $ticket
                    ->mine();
                if ( in_array( self::I_AM_OWNER, $flags ) )
                {
                    $ticket
                        ->where( 'owner_id', '=', \Auth::user()->id );
                }
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

    public function scopeOverdue ( $query )
    {
        return $query
            ->whereIn( 'status_code', [ 'transferred', 'transferred_again', 'accepted', 'assigned', 'in_process', 'completed_with_act', 'completed_without_act' ] )
            ->whereHas( 'ticket', function ( $ticket )
            {
                return $ticket
                    ->overdue();
            });
    }

    public function scopeNotFinaleStatuses ( $query )
    {
        return $query
            ->whereNotIn( self::$_table . '.status_code', Ticket::$final_statuses );
    }

    public function scopeSearch ( $query, Request $request )
    {

        if ( \Auth::user()->can( 'tickets.search' ) )
        {

            $types = [];
            $managements = [];
            $operators = [];
            $statuses = [];

            if ( ! empty( $request->get( 'statuses' ) ) )
            {
                $statuses = explode( ',', $request->get( 'statuses' ) );
            }

            if ( ! empty( $request->get( 'types' ) ) )
            {
                $types = explode( ',', $request->get( 'types' ) );
            }

            if ( ! empty( $request->get( 'operators' ) ) )
            {
                $operators = explode( ',', $request->get( 'operators' ) );
            }

            if ( ! empty( $request->get( 'managements' ) ) )
            {
                $managements = explode( ',', $request->get( 'managements' ) );
            }

            $query
                ->whereHas( 'ticket', function ( $ticket ) use ( $request, $types, $operators )
                {

                    if ( $request->get( 'customer_id' ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.customer_id', '=', $request->get( 'customer_id' ) );
                    }

                    if ( ! empty( $request->get( 'group' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.group_uuid', '=', $request->get( 'group' ) );
                    }

                    if ( ! empty( $request->get( 'tags' ) ) )
                    {
                        $_tags = explode( ',', $request->get( 'tags' ) );
                        $ticket
                            ->whereHas( 'tags', function ( $tags ) use ( $_tags )
                            {
                                $i = 0;
                                foreach ( $_tags as $tag )
                                {
                                    $tag = trim( $tag );
                                    if ( empty( $tag ) ) continue;
                                    $tag = '%' . str_replace( ' ', '%', $tag ) . '%';
                                    if ( $i ++ == 0 )
                                    {
                                        $tags
                                            ->where( 'text', 'LIKE', $tag );
                                    }
                                    else
                                    {
                                        $tags
                                            ->orWhere( 'text', 'LIKE', $tag );
                                    }
                                }
                                return $tags;
                            });
                    }

                    if ( \Auth::user()->can( 'tickets.search' ) )
                    {

                        if ( count( $types ) )
                        {
                            $ticket
                                ->whereIn( Ticket::$_table . '.type_id', $types );
                        }

                        if ( count( $operators ) )
                        {
                            $ticket
                                ->whereIn( Ticket::$_table . '.author_id', $operators );
                        }

                        if ( ! empty( $request->get( 'phone' ) ) )
                        {
                            $p = str_replace( '+7', '', $request->get( 'phone' ) );
                            $p = preg_replace( '/[^0-9_]/', '', $p );
                            $p = '%' . mb_substr( $p, - 10 ) . '%';
                            $ticket
                                ->where( function ( $q ) use ( $p )
                                {
                                    return $q
                                        ->where( Ticket::$_table . '.phone', 'like', $p )
                                        ->orWhere( Ticket::$_table . '.phone2', 'like', $p );
                                });
                        }

                        if ( ! empty( $request->get( 'firstname' ) ) )
                        {
                            $ticket
                                ->where( Ticket::$_table . '.firstname', 'like', '%' . str_replace( ' ', '%', $request->get( 'firstname' ) ) . '%' );
                        }

                        if ( ! empty( $request->get( 'middlename' ) ) )
                        {
                            $ticket
                                ->where( Ticket::$_table . '.middlename', 'like', '%' . str_replace( ' ', '%', $request->get( 'middlename' ) ) . '%' );
                        }

                        if ( ! empty( $request->get( 'lastname' ) ) )
                        {
                            $ticket
                                ->where( Ticket::$_table . '.lastname', 'like', '%' . str_replace( ' ', '%', $request->get( 'lastname' ) ) . '%' );
                        }

                        if ( ! empty( $request->get( 'emergency' ) ) )
                        {
                            $ticket
                                ->where( Ticket::$_table . '.emergency', '=', 1 );
                        }

                        if ( ! empty( $request->get( 'dobrodel' ) ) )
                        {
                            $ticket
                                ->where( Ticket::$_table . '.dobrodel', '=', 1 );
                        }

                        if ( ! empty( $request->get( 'from_lk' ) ) )
                        {
                            $ticket
                                ->where( Ticket::$_table . '.from_lk', '=', 1 );
                        }

                        if ( ! empty( $request->get( 'overdue_acceptance' ) ) )
                        {
                            $ticket
                                ->whereRaw( Ticket::$_table . '.deadline_acceptance < COALESCE( accepted_at, CURRENT_TIMESTAMP )' );
                        }

                        if ( ! empty( $request->get( 'overdue_execution' ) ) )
                        {
                            $ticket
                                ->whereRaw( Ticket::$_table . '.deadline_execution < COALESCE( completed_at, CURRENT_TIMESTAMP )' );
                        }

                    }

                    if ( ! empty( $request->get( 'ticket_id' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.id', '=', $request->get( 'ticket_id' ) );
                    }

                    if ( ! empty( $request->get( 'created_from' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.created_at >= ?', [ Carbon::parse( $request->get( 'created_from' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'created_to' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.created_at <= ?', [ Carbon::parse( $request->get( 'created_to' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'accepted_from' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.accepted_at >= ?', [ Carbon::parse( $request->get( 'accepted_from' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'accepted_to' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.accepted_at <= ?', [ Carbon::parse( $request->get( 'accepted_to' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'completed_from' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.completed_at >= ?', [ Carbon::parse( $request->get( 'completed_from' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'completed_to' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.completed_at <= ?', [ Carbon::parse( $request->get( 'completed_to' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'postponed_from' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.postponed_at >= ?', [ Carbon::parse( $request->get( 'postponed_from' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'postponed_to' ) ) )
                    {
                        $ticket
                            ->whereRaw( Ticket::$_table . '.postponed_at <= ?', [ Carbon::parse( $request->get( 'postponed_to' ) )->toDateTimeString() ] );
                    }

                    if ( ! empty( $request->get( 'operator_id' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.author_id', '=', $request->get( 'operator_id' ) );
                    }

                    if ( ! empty( $request->get( 'building_id' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.building_id', '=', $request->get( 'building_id' ) );
                    }

                    if ( ! empty( $request->get( 'segments' ) ) )
                    {
                        $segments = Segment::whereIn( 'id', $request->get( 'segments' ) )->get();
                        if ( $segments->count() )
                        {
                            $segmentIds = [];
                            foreach ( $segments as $segment )
                            {
                                $segmentChilds = new SegmentChilds( $segment );
                                $segmentIds += $segmentChilds->ids;
                            }
                            $ticket
                                ->whereHas( 'building', function ( $building ) use ( $segmentIds )
                                {
                                    return $building
                                        ->whereIn( Building::$_table . '.segment_id', $segmentIds );
                                });
                        }
                    }

                    if ( ! empty( $request->get( 'flat' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.flat', '=', $request->get( 'flat' ) );
                    }

                    if ( ! empty( $request->get( 'actual_building_id' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.actual_building_id', '=', $request->get( 'actual_building_id' ) );
                    }

                    if ( ! empty( $request->get( 'actual_flat' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.actual_flat', '=', $request->get( 'actual_flat' ) );
                    }

                    if ( ! empty( $request->get( 'provider_id' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.provider_id', '=', $request->get( 'provider_id' ) );
                    }

                    if ( ! empty( $request->get( 'vendor_id' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.vendor_id', '=', $request->get( 'vendor_id' ) );
                    }

                    if ( ! empty( $request->get( 'vendor_date' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.vendor_date', '=', $request->get( 'vendor_date' ) );
                    }

                    if ( ! empty( $request->get( 'vendor_number' ) ) )
                    {
                        $ticket
                            ->where( Ticket::$_table . '.vendor_number', '=', $request->get( 'vendor_number' ) );
                    }

                    if ( $request->get( 'show' ) == 'owner' )
                    {
                        $ticket
                            ->where( 'owner_id', '=', \Auth::user()->id );
                    }

                });

            if ( count( $statuses ) )
            {
                $query
                    ->whereIn( TicketManagement::$_table . '.status_code', $statuses );
            }

            if ( count( $managements ) )
            {
                $query
                    ->whereIn( TicketManagement::$_table . '.management_id', $managements );
            }

            if ( ! empty( $request->get( 'rate' ) ) )
            {
                $query
                    ->where( TicketManagement::$_table . '.rate', '=', $request->get( 'rate' ) );
            }

            if ( ! empty( $request->get( 'ticket_management_id' ) ) )
            {
                $query
                    ->where( TicketManagement::$_table . '.id', '=', $request->get( 'ticket_management_id' ) );
            }

            if ( ! empty( $request->get( 'executor_id' ) ) )
            {
                $query
                    ->where( TicketManagement::$_table . '.executor_id', '=', $request->get( 'executor_id' ) );
            }

        }

        switch ( $request->get( 'show' ) )
        {
            case 'overdue':
                $query
                    ->overdue();
                break;
            case 'call':
                $query
                    ->select(
                        TicketManagement::$_table . '.*',
                        Ticket::$_table . '.completed_at'
                    )
                    ->join( Ticket::$_table, Ticket::$_table . '.id', '=', TicketManagement::$_table . '.ticket_id' )
                    ->whereIn( TicketManagement::$_table . '.status_code', [ 'completed_with_act', 'completed_without_act', 'not_verified' ] )
                    ->orderBy( Ticket::$_table . '.completed_at', 'asc' );
                break;
            case 'not_processed':
                $query
                    ->notProcessed()
                    ->orderBy( TicketManagement::$_table . '.ticket_id', 'desc' );
                break;
            case 'in_process':
                $query
                    ->inProcess()
                    ->orderBy( TicketManagement::$_table . '.ticket_id', 'desc' );
                break;
            case 'completed':
                $query
                    ->completed()
                    ->orderBy( TicketManagement::$_table . '.ticket_id', 'desc' );
                break;
            case 'closed':
                $query
                    ->closed()
                    ->orderBy( TicketManagement::$_table . '.ticket_id', 'desc' );
                break;
            default:
                $query
                    ->orderBy( TicketManagement::$_table . '.ticket_id', 'desc' );
                break;
        }

        if ( ! empty( $request->get( 'scheduled_from' ) ) )
        {
            $query
                ->whereRaw( TicketManagement::$_table . '.scheduled_begin >= ?', [ Carbon::parse( $request->get( 'scheduled_from' ) )->toDateTimeString() ] );
        }

        if ( ! empty( $request->get( 'scheduled_to' ) ) )
        {
            $query
                ->whereRaw( TicketManagement::$_table . '.scheduled_end <= ?', [ Carbon::parse( $request->get( 'scheduled_to' ) )->toDateTimeString() ] );
        }

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

    public function getBackgroundClass ()
    {
        return $this->ticket->getBackgroundClass( $this->status_code );
    }

    public function getTicketNumber ()
    {
        return $this->ticket_id . '/' . $this->id;
    }

    public function canRate ()
    {
        if ( is_null( $this->can_rate ) )
        {
            if ( \Auth::user()->can( 'tickets.rate' ) /*&& ! $this->rate && in_array( $this->status_code, [ 'confirmation_client' ] )*/ )
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

        \DB::beginTransaction();

        if ( $this->status_code != $status_code )
        {

            $status_name = Ticket::$statuses[ $status_code ];
            $log = $this->addLog( 'Статус изменен с "' . $this->status_name . '" на "' . $status_name . '"' );
            if ( $log instanceof MessageBag )
            {
                return $log;
            }
            $statusHistory = StatusHistory::create([
                'model_id'          => $this->id,
                'model_name'        => get_class( $this ),
                'status_code'       => $status_code,
                'status_name'       => $status_name,
            ]);
            if ( $statusHistory instanceof MessageBag )
            {
                return $statusHistory;
            }
            $statusHistory->save();

            $this->status_code = $status_code;
            $this->status_name = $status_name;
            $this->save();
        }

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

            case 'in_process':

                if ( $this->mosreg_id )
                {
                    if ( $this->management->hasMosreg( $management ) )
                    {
                        $mosreg = new Mosreg( $management->mosreg_username, $management->mosreg_password );
                        $mosreg->changeStatus( $this->mosreg_id, 'IN_WORK' );
                    }
                }

                \Cache::tags( 'tickets.scheduled.now' )->flush();

                break;

            case 'accepted':

                $res = $this->changeTicketStatus();
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

                $this->sendTelegramChangeStatus();

                break;
				
            case 'assigned':

                $res = $this->changeTicketStatus();
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

                $message = '<em>Назначен исполнитель</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Адрес проблемы: ' . $ticket->getAddress( true ) . '</b>' . PHP_EOL;
                $message .= 'Тип заявки: ' . $ticket->type->name . PHP_EOL;
                $message .= 'Изменения внес: ' . \Auth::user()->getName( true ) . PHP_EOL . PHP_EOL;

                if ( $this->executor )
                {
                    $message .= 'Исполнитель: ' . $this->executor->name . PHP_EOL;
                }

                $message .= PHP_EOL . $this->getUrl() . PHP_EOL;

                $this->sendTelegram( $message, true );

                \Cache::tags( 'tickets.scheduled.now' )->flush();

                break;
				
            case 'completed_with_act':
            case 'completed_without_act':
            case 'confirmation_operator':
            case 'confirmation_client':
            case 'not_verified':

                $res = $this->changeTicketStatus();
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

                $this->sendTelegramChangeStatus();

                break;

            case 'waiting':

                $this->executor_id = null;
                $this->save();

                $res = $this->changeTicketStatus();
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

                $this->sendTelegramChangeStatus();

                \Cache::tags( 'tickets.scheduled.now' )->flush();

                break;

            case 'closed_with_confirm':
            case 'closed_without_confirm':

                $res = $this->changeTicketStatus();
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

                $res = $this->changeTicketStatus();
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

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
            $url = $this->management->provider->ssl ? 'https://' : 'http://';
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

    public function needAct ()
    {
        if ( ! $this->management->need_act )
        {
            return false;
        }
        else
        {
            return $this->ticket->needAct();
        }
    }

}
