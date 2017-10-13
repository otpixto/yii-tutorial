<?php

namespace App\Models;

use App\Models\Asterisk\Cdr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
use Telegram\Bot\Exceptions\TelegramResponseException;

class Ticket extends BaseModel
{

    protected $table = 'tickets';

    public static $name = 'Заявка';

    private $can_edit = null;
    private $can_comment = null;
    private $can_group = null;

    private $availableStatuses = null;

    private $_call = '-1';

    public static $places = [
        1 => 'Помещение',
        2 => 'Здание',
        3 => 'Двор',
        4 => 'Дорога',
        5 => 'Сквер',
    ];

    public static $statuses = [
        'draft'					            => 'Черновик',
        'created'                           => 'Принято оператором ЕДС',
        'transferred'                       => 'Передано в ЭО',
        'transferred_again'                 => 'Передано в ЭО повторно',
        'accepted'                          => 'Принято к исполнению',
        'assigned'                          => 'Назначен исполнитель',
        'completed_with_act'		        => 'Выполнено с актом',
        'completed_without_act'		        => 'Выполнено без акта',
        'closed_with_confirm'		        => 'Закрыто с подтверждением',
        'closed_without_confirm'	        => 'Закрыто без подтверждения',
        'not_verified'                      => 'Проблема не потверждена',
        'waiting'	                        => 'Отложено',
        'cancel'				            => 'Отмена',
        'rejected'                          => 'Отклонено',
        'no_contract'                       => 'Отказ (нет договора с ЭО)',
    ];

    public static $not_notify = [
        'draft',
        'created',
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
            'no_contract',
        ],
        'created' => [
            'transferred',
            'cancel',
            'no_contract',
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

    protected $nullable = [
        'customer_id',
        'phone2',
        'flat',
        'managements',
        'actual_address_id',
        'actual_flat',
    ];

    public static $rules = [
        'type_id'                   => 'required|integer',
        'address_id'                => 'required|integer',
        'actual_address_id'         => 'nullable|integer',
        'place_id'                  => 'required|integer',
        'flat'                      => 'nullable',
        'actual_flat'               => 'nullable',
        'emergency'                 => 'boolean',
        'urgently'                  => 'boolean',
        'dobrodel'                  => 'boolean',
        'phone'                     => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        'phone2'                    => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        'firstname'                 => 'required',
        'middlename'                => 'nullable',
        'lastname'                  => 'nullable',
        'customer_id'               => 'nullable|integer',
        'text'                      => 'required',
        'managements'               => 'required|array',
    ];

    protected $fillable = [
        'type_id',
        'address_id',
        'actual_address_id',
        'flat',
        'actual_flat',
        'emergency',
        'urgently',
        'dobrodel',
        'phone',
        'phone2',
        'firstname',
        'middlename',
        'lastname',
        'customer_id',
        'place_id',
        'text',
    ];

    public function managements ()
    {
        return $this->hasMany( 'App\Models\TicketManagement' );
    }

    public function address ()
    {
        return $this->belongsTo( 'App\Models\Address' );
    }

    public function actualAddress ()
    {
        return $this->belongsTo( 'App\Models\Address' );
    }

    public function type ()
    {
        return $this->belongsTo( 'App\Models\Type' );
    }

    public function parent ()
    {
        return $this->belongsTo( 'App\Models\Ticket', 'parent_id' );
    }

    public function customer ()
    {
        return $this->belongsTo( 'App\Models\Customer' );
    }

    public function childs ()
    {
        return $this->hasMany( 'App\Models\Ticket', 'parent_id' )
            ->orderBy( 'id', 'desc' );
    }

    public function group ()
    {
        return $this->hasMany( 'App\Models\Ticket', 'group_uuid', 'group_uuid' );
    }

    public function statuses ()
    {
        return $this->hasMany( 'App\Models\Status', 'model_id' )
            ->where( 'model_name', '=', get_class( $this ) );
    }

    public function statusesHistory ()
    {
        return $this->hasMany( 'App\Models\StatusHistory', 'model_id' )
            ->where( 'model_name', '=', get_class( $this ) );
    }

    public function call ()
    {
        if ( $this->_call == '-1' )
        {
            if ( ! $this->call_phone || ! $this->call_at ) return null;
            $dt_from = Carbon::parse( $this->call_at )->subSeconds( \Config::get( 'asterisk.tolerance' ) );
            $dt_to = Carbon::parse( $this->call_at )->addSeconds( \Config::get( 'asterisk.tolerance' ) );
            $this->_call = Cdr
                ::whereRaw( 'RIGHT( src, 10 ) = ?', [ $this->call_phone ] )
                ->answered()
                ->incoming()
                ->whereHas( 'queueLog', function ( $q )
                {
                    return $q
                        ->completed();
                })
                ->whereBetween( 'calldate', [ $dt_from->toDateTimeString(), $dt_to->toDateTimeString() ] )
                ->orderBy( 'id', 'desc' )
                ->first();
        }
        return $this->_call;
    }

    public function scopeNotFinaleStatuses ( $query )
    {
        return $query
            ->whereNotIn( 'status_code', self::$final_statuses );
    }

    public function scopeMine ( $query, $ignoreStatuses = false )
    {
        return $query
            ->where( function ( $q ) use ( $ignoreStatuses )
            {
                if ( \Auth::user()->can( 'tickets.all' ) )
                {
                    $q
                        ->where( function ( $q2 )
                        {
                            return $q2
                                ->where( 'author_id', '=', \Auth::user()->id )
                                ->orWhere( 'status_code', '!=', 'draft' );
                        });
                }
                else if ( \Auth::user()->can( 'tickets.show' ) )
                {
                    if ( ! $ignoreStatuses )
                    {
                        $q
                            ->whereIn( 'status_code', \Auth::user()->getAvailableStatuses() );
                    }
                    $q
                        ->where( function ( $q2 )
                        {
                            return $q2
                                ->whereHas( 'managements', function ( $q3 )
                                {
                                    return $q3
                                        ->whereIn( 'management_id', \Auth::user()->managements->pluck( 'id' ) );
                                })
                                ->orWhere( 'author_id', '=', \Auth::user()->id );
                        });
                }
                else
                {
                    $q->whereNull( 'id' );
                }
                return $q;
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
                    ->where( 'id', '=', $eq )
                    ->orWhere( 'firstname', 'like', $like )
                    ->orWhere( 'middlename', 'like', $like )
                    ->orWhere( 'lastname', 'like', $like )
                    ->orWhere( 'phone', '=', mb_substr( preg_replace( '/\D/', '', $eq ), - 10 ) )
                    ->orWhere( 'phone2', '=', mb_substr( preg_replace( '/\D/', '', $eq ), - 10 ) )
                    ->orWhere( 'text', 'like', $like )
                    ->orWhere( 'flat', '=', $eq )
                    ->orWhereHas( 'author', function ( $q2 ) use ( $like )
                    {
                        return $q2
                            ->where( 'firstname', 'like', $like )
                            ->orWhere( 'middlename', 'like', $like )
                            ->orWhere( 'lastname', 'like', $like );
                    })
                    ->orWhereHas( 'address', function ( $q2 ) use ( $like )
                    {
                        return $q2->where( 'name', 'like', $like );
                    })
                    ->orWhereHas( 'managements', function ( $q2 ) use ( $like )
                    {
                        return $q2->whereHas( 'management', function ( $q3 ) use ( $like )
                        {
                            return $q3->where( 'name', 'like', $like );
                        });
                    })
                    ->orWhereHas( 'type', function ( $q2 ) use ( $like )
                    {
                        return $q2->where( 'name', 'like', $like );
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
            ->whereNull( 'parent_id' );
    }

    public static function create ( array $attributes = [] )
    {

        $attributes['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $attributes['phone'] ) ), -10 );
        if ( !empty( $attributes['phone2'] ) )
        {
            $attributes['phone2'] = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $attributes['phone2'] ) ), -10 );
        }

        $ticket = new Ticket( $attributes );
        $ticket->author_id = Auth::user()->id;
        $ticket->save();

        $res = $ticket->addLog( 'Добавлена заявка' );
        if ( $res instanceof MessageBag )
        {
            return $res;
        }

        return $ticket;

    }
	
	public function edit ( array $attributes = [] )
	{
        if ( !empty( $attributes['phone'] ) )
        {
            $attributes['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $attributes['phone'] ) ), -10 );
        }
        if ( !empty( $attributes['phone2'] ) )
        {
            $attributes['phone2'] = mb_substr( preg_replace( '/[^0-9]/', '', str_replace( '+7', '', $attributes['phone2'] ) ), -10 );
        }
		if ( $this->status_code != 'draft' )
		{
			$res = $this->saveLogs( $attributes );
			if ( $res instanceof MessageBag )
			{
				return $res;
			}
		}
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
		$this->save();
		return $this;
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
                        $this->availableStatuses[ $status_code ] = self::$statuses[ $status_code ];
                    }
                }
            }
        }
        return $this->availableStatuses;
    }

    public function getAddress ( $with_place = false )
    {
        $addr = '';
        if ( $this->address )
        {
            $addr .= $this->address->getAddress();
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
        if ( $this->actualAddress )
        {
            $addr .= $this->actualAddress->name;
        }
        if ( $this->actual_flat )
        {
            $addr .= ', кв. ' . $this->actual_flat;
        }
        return $addr;
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

                if ( $this->type->period_acceptance )
                {

                    $dt = Carbon::parse( $this->created_at );
                    $dt->addSeconds( $this->type->period_acceptance * 60 * 60 );

                    if ( $now->timestamp > $dt->timestamp )
                    {
                        return 'color-red';
                    }

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

    public function getClass ()
    {

        $now = Carbon::now();

        switch ( $this->status_code )
        {

            case 'transferred':
            case 'transferred_again':

                if ( $this->type->period_acceptance )
                {
                    $status_transferred = $this->statusesHistory->whereIn( 'status_code', [ 'transferred', 'transferred_again' ] )->first();
                    if ( $status_transferred )
                    {
                        $dt = $status_transferred->created_at;
                        $dt->addMinutes( $this->type->period_acceptance * 60 );
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

                /*if ( $this->type->period_execution )
                {

                    $status_accepted = $this->statusesHistory->where( 'status_code', 'accepted' )->first();
                    if ( $status_accepted )
                    {
                        $dt = $status_accepted->created_at;
                        $dt->addMinutes( $this->type->period_execution * 60 );
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

    public function getStatus ( $html = false )
    {
        if ( $html )
        {
            return '<div class="' . $this->getClass() . '">' . $this->status_name . '</div>';
        }
        else
        {
            return $this->status_name;
        }
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

    public function canComment ()
    {
        if ( is_null( $this->can_comment ) )
        {
            if ( \Auth::user()->can( 'tickets.comment_add' ) && $this->managements()->mine()->whereHas( 'management', function ( $q ){ return $q->where( 'has_contract', '=', 1 ); } )->count() )
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

    public function canGroup ()
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

        if ( !$force && ! in_array( $status_code, self::$workflow[ $this->status_code ] ?? [] ) )
        {
            return new MessageBag([ 'Невозможно сменить статус!' ]);
        }

        \DB::beginTransaction();

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

        $res = $this->processStatus();

        if ( $res instanceof MessageBag )
        {
            return $res;
        }

        $group = $this
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
        }

        \DB::commit();

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

                $message = '<em>Обращение отменено</em>' . PHP_EOL . PHP_EOL;

                $message .= '<b>Номер обращения: ' . $this->id . '</b>' . PHP_EOL;

                $this->sendTelegram( $message );

                break;

            case 'transferred':

                $res = $this->changeManagementsStatus([
                    'created'
                ]);
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

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

    public function sendTelegram ( $message = null )
    {

        if ( empty( $message ) || in_array( $this->status_code, self::$not_notify ) ) return;

        foreach ( $this->managements as $ticketManagement )
        {
            $management = $ticketManagement->management;
            if ( ! $management->has_contract ) continue;
            foreach ( $management->subscriptions as $subscription )
            {
                try
                {
                    \Telegram::sendMessage([
                        'chat_id'                   => $subscription->telegram_id,
                        'text'                      => $message,
                        'parse_mode'                => 'html',
                        'disable_web_page_preview'  => true
                    ]);
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

}
