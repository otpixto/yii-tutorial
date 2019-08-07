<?php

namespace App\Models;

use App\Classes\GzhiHandler;
use App\Jobs\GzhiJob;
use App\Jobs\SendPush;
use App\Jobs\SendStream;
use App\Models\Asterisk\Cdr;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;

class Ticket extends BaseModel
{

    use DispatchesJobs;

    protected $table = 'tickets';
    public static $_table = 'tickets';

    public static $name = 'Заявка';

    private $can_edit = null;
    private $can_group = null;
    private $can_call = null;
    private $can_create_user = null;
    private $can_rate = null;

    private $availableStatuses = null;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'transferred_at',
        'accepted_at',
        'completed_at',
        'deadline_acceptance',
        'deadline_execution',
        'postponed_to',
        'vendor_date',
    ];

    public static $places = [
        1 => 'Помещение',
        2 => 'Здание',
        3 => 'Двор',
        4 => 'Дорога',
        5 => 'Сквер',
    ];

    public static $statuses = [
        'draft'					            => 'Черновик',
        'created'                           => 'Нераспределенная',
        'moderate'                          => 'На модерации',
        'from_dobrodel'                     => 'Из добродела',
        'transferred'                       => 'Ожидает принятия Ответственным',
        'transferred_again'                 => 'Требует доработки',
        'accepted'                          => 'Не назначен исполнитель',
        'assigned'                          => 'Назначен исполнитель',
        'completed_with_act'		        => 'Выполнено с актом',
        'completed_without_act'		        => 'Выполнено без акта',
        'closed_with_confirm'		        => 'Закрыта с подтверждением',
        'closed_without_confirm'	        => 'Закрыта без подтверждения',
        'not_verified'                      => 'Проблема не подтверждена',
        'waiting'	                        => 'Отложено',
        'cancel'				            => 'Отменена',
        'rejected'                          => 'Отклонена управляющим',
        'rejected_operator'                 => 'Отклонена оператором',
        'no_contract'                       => 'Отказ (нет договора с УО)',
        'in_process'                        => 'В работе',
        'archive'                           => 'Архив',
        'confirmation_operator'             => 'Ожидает подтверждения центром',
        'confirmation_client'             	=> 'Ожидает подтверждения клиентом',
        'conflict'             	            => 'Конфликтная',
        'GZI_REMEDY'                        => 'Контроль ГЖИ: Устранение',
        'GZI_REMEDY_ANSWER'                 => 'Контроль ГЖИ: Получен ответ УК',
        'GZI_EXPIRED'                       => 'Контроль ГЖИ: Просрочено',
        'GZI_EXTRA_AUDIT'                   => 'Контроль ГЖИ: Внеплановая проверка',
        'GZI_SOLVED'                        => 'Закрыто (ГЖИ)',
        'UNSATISFIED_SENDED_TO_DD'          => 'Отправлено в добродел',
    ];

    public static $statuses_buttons = [
        'rejected' => [
            'name'          => 'Отклонить',
            'class'         => 'red-soft',
        ],
        'cancel' => [
            'name'          => 'Отменить',
            'class'         => 'red-soft',
        ],
        'no_contract' => [
            'name'          => 'Отказ (нет договора с УО)',
            'class'         => 'dark',
        ],
        'transferred' => [
            'name'          => 'Передать в УО',
            'class'         => 'green-soft',
        ],
        'transferred_again' => [
            'name'          => 'Требует доработки',
            'class'         => 'yellow-soft',
        ],
        'accepted' => [
            'name'          => 'Принять',
            'class'         => 'green-soft',
        ],
        'assigned' => [
            'name'          => 'Назначить исполнителя',
            'class'         => 'green-soft',
        ],
        'in_process' => [
            'name'          => 'Начать работу',
            'class'         => 'green-soft',
        ],
        'completed_with_act' => [
            'name'          => 'Выполнено с актом',
            'class'         => 'green-soft',
        ],
        'completed_without_act' => [
            'name'          => 'Выполнено без акта',
            'class'         => 'green-soft',
        ],
        'waiting' => [
            'name'          => 'Отложить',
            'class'         => 'yellow-soft',
        ],
        'not_verified' => [
            'name'          => 'Проблема не подтверждена',
            'class'         => 'yellow-soft',
        ],
        'confirmation_operator' => [
            'name'          => 'Передать в центр для подтверждения',
            'class'         => 'green-soft',
        ],
    ];

    public static $not_notify = [
        'draft',
        'created',
        'moderate',
        'from_dobrodel',
        'cancel',
        'completed_with_act',
        'completed_without_act',
        'not_verified',
    ];

    public static $final_statuses = [
        'closed_with_confirm',
        'closed_without_confirm',
        'cancel',
        'no_contract',
        'rejected',
    ];

    public static $without_time = [
        'cancel',
        'no_contract',
        'refected',
    ];

    public static $workflow = [
        'draft' => [
            'created',
        ],
        'created' => [
            'transferred',
            'cancel',
        ],
        'moderate' => [
            'created',
            'cancel',
        ],
        'from_dobrodel' => [
            'created',
            'cancel',
        ],
        'transferred' => [
            'cancel',
        ],
        'transferred_again' => [
            'cancel',
        ],
        'accepted' => [
            'cancel',
        ],
    ];

    public static $mosreg_statuses = [
        'NEW_CLAIM'                     => 'transferred',
        'IN_WORK'                       => 'transferred',
        'ANSWERED'                      => 'confirmation_client',
        'EXPIRED'                       => 'waiting',
        'UNSATISFIED'                   => 'transferred_again',
        'SOLVED'                        => 'GZI_SOLVED',
        'UNSATISFIED_SENDED_TO_DD'      => 'UNSATISFIED_SENDED_TO_DD',
        'GZI_REMEDY'                    => 'GZI_REMEDY',
        'GZI_REMEDY_ANSWER'             => 'GZI_REMEDY_ANSWER',
        'GZI_EXPIRED'                   => 'GZI_EXPIRED',
        'GZI_EXTRA_AUDIT'               => 'GZI_EXTRA_AUDIT',
    ];

    protected $nullable = [
        'provider_id',
        'vendor_id',
        'vendor_date',
        'vendor_number',
        'customer_id',
        'phone2',
        'flat',
        'managements',
        'actual_building_id',
        'actual_flat',
        'postponed_to',
        'scheduled_begin',
        'scheduled_end',
        'time_from',
        'time_to',
    ];

    protected $fillable = [
        'author_id',
        'provider_id',
        'vendor_id',
        'vendor_date',
        'vendor_number',
        'type_id',
        'building_id',
        'flat',
        'actual_building_id',
        'actual_flat',
        'emergency',
        'urgently',
        'dobrodel',
        'from_lk',
        'from_mosreg',
        'phone',
        'phone2',
        'firstname',
        'middlename',
        'lastname',
        'customer_id',
        'place_id',
        'text',
        'scheduled_begin',
        'scheduled_end',
        'postponed_to',
        'time_from',
        'time_to',
    ];

    public function managements ()
    {
        return $this->hasMany( TicketManagement::class );
    }

    public function building ()
    {
        return $this->belongsTo(Building::class );
    }

    public function actualBuilding ()
    {
        return $this->belongsTo(Building::class );
    }

    public function type ()
    {
        return $this->belongsTo( Type::class );
    }

    public function gzhiRequest ()
    {
        return $this->hasOne( GzhiRequest::class );
    }

    public function vendor ()
    {
        return $this->belongsTo( Vendor::class );
    }

    public function parent ()
    {
        return $this->belongsTo( Ticket::class );
    }

    public function customer ()
    {
        return $this->belongsTo( Customer::class, 'phone', 'phone' );
    }

    public function customerTickets ()
    {
        return $this->hasMany( Ticket::class, 'phone', 'phone' );
    }

    public function neighborsTickets ()
    {
        return $this->hasMany( Ticket::class, 'building_id', 'building_id' );
    }

    public function childs ()
    {
        return $this->hasMany( Ticket::class, 'parent_id' )
            ->orderBy( Ticket::$_table . '.id', 'desc' );
    }

    public function group ()
    {
        return $this->hasMany( Ticket::class, 'group_uuid', 'group_uuid' );
    }

    public function statuses ()
    {
        return $this->hasMany( Status::class, 'model_id' )
            ->where( Status::$_table . '.model_name', '=', get_class( $this ) );
    }

    public function statusesHistory ()
    {
        return $this->hasMany( StatusHistory::class, 'model_id' )
            ->where( StatusHistory::$_table . '.model_name', '=', get_class( $this ) );
    }

    public function calls ()
    {
        return $this->hasMany( TicketCall::class );
    }

    public function cdr ()
    {
        return $this->belongsTo( Cdr::class, 'call_id', 'uniqueid' );
    }

    public function scopeNotFinaleStatuses ( $query )
    {
        return $query
            ->whereNotIn( self::$_table . '.status_code', self::$final_statuses );
    }

    public function scopeDraft ( $query, $user_id = null )
    {
        return $query
            ->where( self::$_table . '.author_id', '=', $user_id ?: \Auth::user()->id )
            ->where( self::$_table . '.provider_id', '=', Provider::getCurrent()->id )
            ->where( self::$_table . '.status_code', '=', 'draft' );
    }

    public function scopeOverdue ( $query )
    {
        return $query
            ->where( function ( $q )
            {
                return $q
                    ->whereRaw( 'COALESCE( accepted_at, CURRENT_TIMESTAMP ) > deadline_acceptance' )
                    ->orWhereRaw( 'COALESCE( completed_at, CURRENT_TIMESTAMP ) > deadline_execution' )
                    ->orWhereRaw( 'COALESCE( postponed_to, CURRENT_TIMESTAMP ) < CURRENT_TIMESTAMP' );
            });
    }

    public function scopeOverdueAcceptance ( $query )
    {
        return $query
            ->whereRaw( 'COALESCE( accepted_at, CURRENT_TIMESTAMP ) > deadline_acceptance' );
    }

    public function scopeOverdueExecution ( $query )
    {
        return $query
            ->where( function ( $q )
            {
                return $q
                    ->whereRaw( 'COALESCE( completed_at, CURRENT_TIMESTAMP ) > deadline_execution' )
                    ->orWhereRaw( 'COALESCE( postponed_to, CURRENT_TIMESTAMP ) < CURRENT_TIMESTAMP' );
            });
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

    public function scopeNotCompleted ( $query )
    {
        return $query
            ->whereNotIn( self::$_table . '.status_code', [ 'completed_with_act', 'completed_without_act', 'not_verified' ] );
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
        return $query
			->mineProvider()
            ->where( function ( $q ) use ( $flags )
            {
                return $q
                    ->where( self::$_table . '.author_id', '=', \Auth::user()->id )
                    ->orWhere( function ( $q2 ) use ( $flags )
                    {
                        if ( ! in_array( self::IGNORE_STATUS, $flags ) && ! \Auth::user()->can( 'supervisor.all_statuses.show' ) )
                        {
                            $q2
                                ->whereIn( Ticket::$_table . '.status_code', \Auth::user()->getAvailableStatuses( 'show' ) );
                        }
                        $q2
                            ->whereHas( 'managements', function ( $managements ) use ( $flags )
                            {
                                if ( ! in_array( self::IGNORE_MANAGEMENT, $flags ) && ! \Auth::user()->can( 'supervisor.all_managements' ) )
                                {
                                    return $managements
                                        ->whereIn( TicketManagement::$_table . '.management_id', \Auth::user()->managements()->pluck( Management::$_table . '.id' ) );
                                }
                            });
                        return $q2;
                    });
            });
    }

    public function scopeFastSearch ( $query, $search )
    {
        $eq = trim( $search );
        $like = '%' . str_replace( ' ', '%', $eq ) . '%';
        return $query
            ->where( function ( $q ) use ( $like, $eq )
            {
                return $q
                    ->where( self::$_table . '.id', '=', $eq )
                    ->orWhere( self::$_table . '.firstname', 'like', $like )
                    ->orWhere( self::$_table . '.middlename', 'like', $like )
                    ->orWhere( self::$_table . '.lastname', 'like', $like )
                    ->orWhere( self::$_table . '.phone', '=', mb_substr( preg_replace( '/\D/', '', $eq ), - 10 ) )
                    ->orWhere( self::$_table . '.phone2', '=', mb_substr( preg_replace( '/\D/', '', $eq ), - 10 ) )
                    ->orWhere( self::$_table . '.text', 'like', $like )
                    ->orWhere( self::$_table . '.flat', '=', $eq )
                    ->orWhereHas( 'author', function ( $author ) use ( $like )
                    {
                        return $author
                            ->where( User::$_table . '.firstname', 'like', $like )
                            ->orWhere( User::$_table . '.middlename', 'like', $like )
                            ->orWhere( User::$_table . '.lastname', 'like', $like );
                    })
                    ->orWhereHas( 'building', function ( $building ) use ( $like )
                    {
                        return $building
                            ->where( Building::$_table . '.name', 'like', $like );
                    })
                    ->orWhereHas( 'managements', function ( $managements ) use ( $like )
                    {
                        return $managements
                            ->whereHas( 'management', function ( $management ) use ( $like )
                            {
                                return $management
                                    ->where( Management::$_table . '.name', 'like', $like );
                            });
                    })
                    ->orWhereHas( 'type', function ( $type ) use ( $like )
                    {
                        return $type
                            ->where( Type::$_table . '.name', 'like', $like );
                    });
            });
    }

    public function scopeGroupped ( $query )
    {
        return $query
            ->addSelect( \DB::raw( 'DISTINCT group_uuid' ) )
            ->addSelect( '*' );
    }

    public function scopeParentsOnly ( $query )
    {
        return $query
            ->whereNull( self::$_table . '.parent_id' );
    }

    public function isFinalStatus ()
    {
        return in_array( $this->status_code, self::$final_statuses );
    }

    public static function create ( array $attributes = [], $emergency = 0 )
    {

        $ticket = self::draft()->first();

        if ( ! $ticket )
        {

            $ticket = parent::create( $attributes );
            if ( $ticket instanceof MessageBag )
            {
                return $ticket;
            }
            $ticket->status_code = 'draft';
            $ticket->status_name = self::$statuses[ 'draft' ];
            $ticket->save();

            $res = $ticket->addLog( 'Создан черновик' );
            if ( $res instanceof MessageBag )
            {
                return $res;
            }

        }
        else
        {
            $ticket->fill( $attributes );
            $ticket->save();
        }

        if ( $emergency )
        {
            //
        }

        return $ticket;

    }

	public function edit ( array $attributes = [] )
	{
        if ( ! empty( $attributes[ 'phone' ] ) )
        {
            $attributes[ 'phone' ] = str_replace( '+7', '', $attributes[ 'phone' ] );
            $attributes[ 'phone' ] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes[ 'phone' ] ), -10 );
        }
        if ( ! empty( $attributes[ 'phone2' ] ) )
        {
            $attributes[ 'phone2' ] = str_replace( '+7', '', $attributes[ 'phone2' ] );
            $attributes[ 'phone2' ] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes[ 'phone2' ] ), -10 );
        }
		if ( $this->status_code != 'draft' )
		{
			$res = $this->saveLogs( $attributes );
			if ( $res instanceof MessageBag )
			{
				return $res;
			}
            if ( ( isset( $attributes[ 'lastname' ] ) || isset( $attributes[ 'firstname' ] ) || isset( $attributes[ 'middlename' ] ) || isset( $attributes[ 'actual_building_id' ] ) || isset( $attributes[ 'actual_flat' ] ) || isset( $attributes[ 'phone' ] ) || isset( $attributes[ 'phone2' ] ) ) && $this->customer )
            {
                $res = $this->customer->edit( $attributes );
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }
            }
		}
        $change_type = ( ! empty( $attributes[ 'type_id' ] ) && $this->type_id != $attributes[ 'type_id' ] );
        $this->fill( $attributes );
		if ( isset( $attributes['param'] ) && $attributes['param'] == 'mark' )
		{
			if ( ! isset( $attributes['emergency'] ) && $this->emergency == 1 )
			{
				$this->emergency = 0;
				$res = $this->saveLog( 'emergency', 1, 0 );
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }
			}
			if ( ! isset( $attributes['urgently'] ) && $this->urgently == 1 )
			{
				$this->urgently = 0;
                $res = $this->saveLog( 'urgently', 1, 0 );
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }
			}
			if ( ! isset( $attributes['dobrodel'] ) && $this->dobrodel == 1 )
			{
				$this->dobrodel = 0;
                $res = $this->saveLog( 'dobrodel', 1, 0 );
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }
			}
		}

        if ( $this->status_code != 'draft' && $this->status_code != 'moderate' && $this->isDirty() )
        {
            $this->dispatch( new SendStream( 'update', $this ) );
        }

        $this->save();

        if ( $change_type && $this->type )
        {
            if ( $this->transferred_at )
            {
                $transferred_at = $this->transferred_at;
                $this->deadline_acceptance = $this->type->period_acceptance ? $transferred_at->addMinutes( $this->type->period_acceptance * 60 ) : $transferred_at;
                $this->deadline_execution = $this->type->period_execution ? $transferred_at->addMinutes( $this->type->period_execution * 60 ) : $transferred_at;
            }
            if ( $this->type->emergency )
            {
                $this->emergency = $this->type->emergency;
            }
            $this->save();
        }

        /*if ( $this->customer && $this->customer->user && $this->customer->user->push_id )
        {
            $this->dispatch( new SendPush( config( 'push.keys.lk' ), $this->customer->user->push_id, 'Заявка отредактирована', 'Заявка отредактирована', 'ticket', $this->id ) );
        }*/

		return $this;
	}

	public function save ( array $options = [] )
    {
        $result = parent::save( $options );

        if(isset($this->building()->first()->name))
        {
            $buildingName = $this->building()->first()->name;

            $gzhiProviders = GzhiApiProvider::get();

            foreach ($gzhiProviders as $gzhiProvider)
            {
                if(strpos($buildingName, $gzhiProvider->name))
                {
                    //(new GzhiHandler)->handleGzhiTicket($this, $gzhiProvider);
                    $this->dispatch( new GzhiJob( $this, $gzhiProvider ) );
                }
            }
        }

        return $result;
    }

    public function getName ()
    {
        $name = [];
        if ( !empty( $this->lastname ) )
        {
            $name[] = $this->lastname;
        }
        if ( !empty( $this->firstname ) )
        {
            $name[] = $this->firstname;
        }
        if ( !empty( $this->middlename ) )
        {
            $name[] = $this->middlename;
        }
        return implode( ' ', $name );
    }

    public function getPhones ( $html = false )
    {
		$phones = '';
		if ( !empty( $this->phone ) )
		{
			$phone = '+7 (' . mb_substr( $this->phone, 0, 3 ) . ') ' . mb_substr( $this->phone, 3, 3 ) . '-' . mb_substr( $this->phone, 6, 2 ). '-' . mb_substr( $this->phone, 8, 2 );
			if ( $html )
			{
				$phones = '<a href="tel:7' . $this->phone . '" class="inherit">' . $phone . '</a';
			}
			else
			{
				$phones = $phone;
			}
		}
        if ( !empty( $this->phone2 ) )
        {
            $phone2 = '+7 (' . mb_substr( $this->phone2, 0, 3 ) . ') ' . mb_substr( $this->phone2, 3, 3 ) . '-' . mb_substr( $this->phone2, 6, 2 ). '-' . mb_substr( $this->phone2, 8, 2 );
            $phones .= '; ';
            if ( $html )
            {
                $phones .= '<a href="tel:7' . $this->phone . '" class="inherit">' . $phone2 . '</a';
            }
            else
            {
                $phones .= $phone2;
            }
        }
        return $phones;
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
                $res[ $status_code ] = Ticket::$statuses[ $status_code ] ?? null;
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

    public function getAddress ( $with_place = false )
    {
        $addr = '';
        if ( $this->building )
        {
            $addr .= $this->building->name . ' (' . $this->building->buildingType->name . ')';
        }
		if ( $this->flat )
		{
			$addr .= ', кв. ' . $this->flat;
		}
		if ( $with_place )
        {
            $addr .= ' (' . self::$places[ $this->place_id ] . ')';
        }
        return $addr;
    }

    public function getActualAddress ()
    {
        $addr = '';
        if ( $this->actualBuilding )
        {
            $addr .= $this->actualBuilding->name;
        }
        if ( $this->actual_flat )
        {
            $addr .= ', кв. ' . $this->actual_flat;
        }
        return $addr;
    }

    public function getComments ()
    {
        $comments = new Collection();
        $comments = $comments->merge( $this->comments );
        foreach ( $this->managements as $item )
        {
            $comments = $comments->merge( $item->comments );
        }
        return $comments->sortBy( 'id' );
    }

    public function getPlace ()
    {
        return self::$places[ $this->place_id ] ?? null;
    }

	public function getColor ()
    {

        $now = Carbon::now();

        switch ( $this->status_code )
        {

            case 'accepted':
            case 'assigned':

                if ( $this->deadline_execution && ( $this->completed_at ?? $now )->timestamp > $this->deadline_execution->timestamp )
                {
                    return 'color-red';
                }

                return 'color-green';

                break;

            case 'not_verified':
            case 'cancel':
            case 'no_contract':
                return 'color-red';
                break;

            case 'transferred':
            case 'transferred_again':
                return 'color-yellow';
                break;

        }

    }

    public function getProgressData ()
    {
        $title = '';
        $percent = 0;
        $class = 'progress-bar';
        if ( $this->deadline_acceptance && $this->deadline_execution && ! $this->isFinalStatus() )
        {
            $now = Carbon::now();
            if ( ! $this->accepted_at && ! $this->completed_at )
            {
                $title = 'Принять до ' . $this->deadline_acceptance->format( 'd.m.Y H:i' );
                if ( $now->timestamp < $this->deadline_acceptance->timestamp )
                {
                    $percent = 100 - ( $this->type->period_acceptance ? ceil( $now->diffInMinutes( $this->deadline_acceptance ) / ( $this->type->period_acceptance * 60 ) * 100 ) : 1 );
                }
                else
                {
                    $percent = 100;
                    $title .= ' (Просрочено)';
                }
            }
            else if ( ! $this->completed_at )
            {
                $title = 'Выполнить до ' . $this->deadline_execution->format( 'd.m.Y H:i' );
                if ( $now->timestamp < $this->deadline_execution->timestamp )
                {
                    $percent = 100 - ( $this->type->period_execution ? ceil( $now->diffInMinutes( $this->deadline_execution ) / ( $this->type->period_execution * 60 ) * 100 ) : 1 );
                }
                else
                {
                    $percent = 100;
                    $title .= ' (Просрочено)';
                }
            }
            else
            {
                $percent = 100;
                $class .= ' progress-bar-primary';
                return compact( 'title', 'percent', 'class' );
            }
            if ( $percent <= 0 ) $percent = 0;
            else if ( $percent >= 100 )
            {
                $percent = 100;
                $class .= ' progress-bar-danger';
            }
            else if ( $percent >= 60 )
            {
                $class .= ' progress-bar-striped active progress-bar-danger';
            }
            else if ( $percent >= 30 )
            {
                $class .= ' progress-bar-striped active progress-bar-warning';
            }
            else
            {
                $class .= ' progress-bar-striped active progress-bar-success';
            }
        }
        else
        {
            $percent = 100;
            $class .= ' progress-bar-primary';
        }
        return compact( 'title', 'percent', 'class' );
    }

    public function overdueDeadlineAcceptance ()
    {
        if ( $this->overdueDeadlinePostponed() && $this->deadline_acceptance && ( $this->accepted_at ?? Carbon::now() )->timestamp > $this->deadline_acceptance->timestamp )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function overdueDeadlineExecution ()
    {
        if ( $this->overdueDeadlinePostponed() || ( $this->deadline_execution && ( $this->completed_at ?? Carbon::now() )->timestamp > $this->deadline_execution->timestamp ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function overdueDeadlinePostponed ()
    {
        if ( $this->status_code == 'waiting' && $this->postponed_to && $this->postponed_to->timestamp < Carbon::now()->timestamp )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function getClass ()
    {
        switch ( $this->status_code )
        {
            case 'transferred':
            case 'transferred_again':
                return $this->overdueDeadlineAcceptance() ? 'danger' : 'warning';
                break;
            case 'accepted':
            case 'assigned':
                return $this->overdueDeadlineExecution() ? 'danger' : 'success';
                break;
            case 'waiting':
                if ( $this->overdueDeadlinePostponed() )
                {
                    return 'danger';
                }
                break;
            case 'not_verified':
            case 'cancel':
            case 'no_contract':
            case 'rejected':
                return 'danger';
                break;
        }
        return '';
    }

    public function getBackgroundClass ( $status_code = null )
    {
        if ( ! $status_code ) $status_code = $this->status_code;
        if ( isset( self::$statuses_buttons[ $status_code ] ) )
        {
            $class = 'bg-' . self::$statuses_buttons[ $status_code ][ 'class' ];
        }
        else
        {
            $class = 'bg-grey-salt';
        }
        if ( $this->overdueDeadlineAcceptance() )
        {
            $class = 'bg-yellow-gold';
        }
        if ( $this->overdueDeadlineExecution() )
        {
            $class = 'bg-red-thunderbird';
        }
        return $class;
    }

    public function getStatus ( $html = false )
    {
        if ( $html )
        {
            return '<div class="' . $this->getBackgroundClass() . '">' . $this->status_name . '</div>';
        }
        else
        {
            return $this->status_name;
        }
    }

    public function canCreateUser ( $force = false )
    {
        if ( $force || is_null( $this->can_create_user ) )
        {
            if ( \Auth::user()->can( 'tickets.create_user' ) && ! $this->from_lk && $this->phone && $this->firstname && $this->lastname && ( ! $this->customer || ! $this->customer->user ) )
            {
                $this->can_create_user = true;
            }
            else
            {
                $this->can_create_user = false;
            }
        }
        return $this->can_create_user;
    }

    public function canEdit ()
    {
        if ( is_null( $this->can_edit ) )
        {
            if ( \Auth::user()->can( 'tickets.edit' ) )
            {
                $this->can_edit = true;
            }
            else
            {
                $this->can_edit = false;
            }
        }
        return $this->can_edit;
    }

    public function canCall ()
    {
        if ( is_null( $this->can_call ) )
        {
            if ( \Auth::user()->can( 'phone' ) && \Auth::user()->openPhoneSession )
            {
                $this->can_call = true;
            }
            else
            {
                $this->can_call = false;
            }
        }
        return $this->can_call;
    }

    public function canGroup () : bool
    {
        if ( is_null( $this->can_group ) )
        {
            if ( \Auth::user()->can( 'tickets.group' ) && $this->status_code != 'draft' && $this->status_code != 'cancel' && $this->status_code != 'no_contract' && $this->status_code != 'closed_with_confirm' && $this->status_code != 'closed_without_confirm' )
            {
                $this->can_group = true;
            }
            else
            {
                $this->can_group = false;
            }
        }
        return $this->can_group;
    }

    public function canRate () : bool
    {
        if ( is_null( $this->can_rate ) )
        {
            foreach ( $this->managements as $ticketManagement )
            {
                if ( $ticketManagement->canRate() )
                {
                    $this->can_rate = true;
                    return true;
                }
            }
            $this->can_rate = false;
        }
        return $this->can_rate;
    }

    public function getStatusHistory ( $status_code )
    {
        if ( ! is_array( $status_code ) ) $status_code = [ $status_code ];
        $this->statusesHistory()->whereIn( 'status_code', $status_code )->orderBy( 'id', 'desc' )->first();
    }

    public function changeStatus ( $status_code, $force = false )
    {

        if ( ! isset( self::$statuses[ $status_code ] ) )
        {
            return new MessageBag([ 'Некорректный статус' ]);
        }

        if ( ! $force )
        {
            $availableStatuses = $this->getAvailableStatuses( 'edit' );
            if ( ! in_array( $status_code, $availableStatuses ) )
            {
                return new MessageBag([ 'Невозможно сменить статус!' ]);
            }
        }

        if ( $this->status_code != $status_code )
        {

            $log = $this->addLog( 'Статус изменен с "' . $this->status_name . '" на "' . self::$statuses[ $status_code ] . '"' );
            if ( $log instanceof MessageBag )
            {
                return redirect()->back()
                    ->withErrors( $log );
            }

            $this->status_code = $status_code;
            $this->status_name = self::$statuses[ $status_code ];
            $this->save();

            $statusHistory = StatusHistory::create([
                'model_id'          => $this->id,
                'model_name'        => get_class( $this ),
                'status_code'       => $status_code,
                'status_name'       => self::$statuses[ $status_code ],
            ]);
            if ( $statusHistory instanceof MessageBag )
            {
                return $statusHistory;
            }

            $statusHistory->save();

            if ( $this->customer && $this->customer->user && $this->customer->user->push_id )
            {
                $this->dispatch( new SendPush( config( 'push.keys.lk' ), $this->customer->user->push_id, 'Заявка отредактирована', 'Статус изменен на "' . $this->status_name . '"', 'ticket', $this->id ) );
            }

        }

        /*$group = $this
            ->group()
            ->where( 'id', '!=', $this->id )
            ->where( 'status_code', '!=', $this->status_code )
            ->get();

        if ( $group->count() )
        {
            foreach ( $group as $row )
            {
                $res = $row->changeStatus( $this->status_code, true );
                if ( $res instanceof MessageBag )
                {
                    return redirect()->back()
                        ->withErrors( $res );
                }
            }
        }*/

        $res = $this->processStatus();
        if ( $res instanceof MessageBag )
        {
            return $res;
        }

    }

    public function processStatus ()
    {

        switch ( $this->status_code )
        {

            case 'cancel':

                $res = $this->changeManagementsStatus();
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

                break;

            case 'transferred':

                $res = $this->changeManagementsStatus([
                    'created'
                ]);
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

                $transferred_at = Carbon::now();

                $this->transferred_at = $transferred_at->toDateTimeString();

                if ( $this->type )
                {
                    $this->deadline_acceptance = $this->type->period_acceptance ? $transferred_at->addMinutes( $this->type->period_acceptance * 60 ) : $transferred_at;
                    $this->deadline_execution = $this->type->period_execution ? $transferred_at->addMinutes( $this->type->period_execution * 60 ) : $transferred_at;
                }

                $this->save();

                break;

            case 'transferred_again':

                $transferred_at = Carbon::now();

                $this->transferred_at = $transferred_at->toDateTimeString();
                $this->completed_at = null;
                $this->duration_work = null;

                if ( $this->type )
                {
                    $this->deadline_acceptance = $this->type->period_acceptance ? $transferred_at->addMinutes( $this->type->period_acceptance * 60 ) : $transferred_at;
                    $this->deadline_execution = $this->type->period_execution ? $transferred_at->addMinutes( $this->type->period_execution * 60 ) : $transferred_at;
                }

                $this->save();

                break;

            case 'accepted':

                $this->accepted_at = Carbon::now()->toDateTimeString();
                $this->save();

                break;

            case 'completed_with_act':
            case 'completed_without_act':
            case 'not_verified':

                $transferred_at = $this->transferred_at;
                $completed_at = Carbon::now();

                if ( ! $this->completed_at )
                {
                    $this->completed_at = $completed_at->toDateTimeString();
                }

                if ( ! $this->duration_work && $transferred_at )
                {
                    $this->duration_work = number_format( $completed_at->diffInMinutes( $transferred_at ) / 60, 2, '.', '' );
                }

                $this->save();

                break;

            case 'closed_with_confirm':
            case 'closed_without_confirm':

                $apply_statuses = self::$statuses;
                unset( $apply_statuses[ 'draft' ] );
                foreach ( self::$final_statuses as $status_code )
                {
                    unset( $apply_statuses[ $status_code ] );
                }

                $res = $this->changeManagementsStatus( array_keys( $apply_statuses ) );
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

                $transferred_at = $this->transferred_at;
                $completed_at = Carbon::now();

                if ( ! $this->completed_at )
                {
                    $this->completed_at = $completed_at->toDateTimeString();
                }

                if ( ! $this->duration_work && $transferred_at )
                {
                    $this->duration_work = number_format( $completed_at->diffInMinutes( $transferred_at ) / 60, 2, '.', '' );
                }

                $this->save();

                break;

        }

    }

    private function changeManagementsStatus ( array $apply_statuses = [] )
    {
        foreach ( $this->managements as $management )
        {
            if ( $management->status_code != $this->status_code && ( count( $apply_statuses ) == 0 || in_array( $management->status_code, $apply_statuses ) ) )
            {
                $res = $management->changeStatus( $this->status_code, true );
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }
            }
        }
    }

    public function sendTelegram ( $message = null, $force = false )
    {

        if ( ! \Config::get( 'telegram.active' ) || empty( $message ) || ( ! $force && in_array( $this->status_code, self::$not_notify ) ) ) return;

        foreach ( $this->managements as $ticketManagement )
        {
            $ticketManagement->sendTelegram( $message, $force );
        }

    }

    public function createCall ( $number_from, $number_to )
    {
        $ticketCall = TicketCall
            ::whereNull( TicketCall::$_table . '.call_id' )
            ->where( TicketCall::$_table . '.author_id', '=', \Auth::user()->id )
            ->where( TicketCall::$_table . '.ticket_id', '=', $this->id )
            ->where( TicketCall::$_table . '.call_phone', '=', $number_to )
            ->where( TicketCall::$_table . '.agent_number', '=', $number_from )
            ->first();
        if ( $ticketCall )
        {
            $ticketCall->created_at = Carbon::now()->toDateTimeString();
        }
        else
        {
            $ticketCall = TicketCall::create([
                'ticket_id'     => $this->id,
                'call_phone'    => $number_to,
                'agent_number'  => $number_from
            ]);
            if ( $ticketCall instanceof MessageBag )
            {
                return $ticketCall;
            }
        }
        $ticketCall->save();
        return $ticketCall;
    }

    public function canComment ()
    {
        return \Auth::user()->can( 'tickets.comments_add' );
    }

    public function needAct ()
    {
        if ( ! $this->type->need_act )
        {
            return false;
        }
        else
        {
            return $this->provider->need_act ?? 0;
        }
    }

}
