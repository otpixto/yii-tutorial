<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;

class Ticket extends BaseModel
{

    protected $table = 'tickets';

    private $can_edit = null;

    public static $places = [
        'Помещение',
        'МОП',
        'Двор',
        'Дорога',
        'Сквер',
    ];

    public static $statuses = [
        null                                => 'Статус не назначен',
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
        'no_contract'                       => 'Отказ (нет договора с ЭО)',
    ];

    public static $final_statuses = [
        'closed_with_confirm',
        'closed_without_confirm',
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
			'transferred_again',
			'cancel',
		],
    ];

    protected $nullable = [
        'management_id',
        'address_id',
        'customer_id',
        'phone2'
    ];

    public static $rules = [
        'type_id'           => 'required|integer',
        'firstname'         => 'required|max:191',
        'middlename'        => 'nullable|max:191',
        'lastname'          => 'nullable|max:191',
        'phone'             => 'required|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        'phone2'            => 'nullable|regex:/\+7 \(([0-9]{3})\) ([0-9]{3})\-([0-9]{2})\-([0-9]{2})/',
        'text'              => 'required|max:191',
        'address'           => 'max:191',
        'emergency'         => 'boolean',
        'urgently'          => 'boolean',
        'dobrodel'          => 'boolean',
        'place'             => 'required|integer',
        'managements'       => 'required|array',
    ];

    protected $fillable = [
        'type_id',
        'firstname',
        'middlename',
        'lastname',
        'phone',
        'phone2',
        'text',
        'address',
        'emergency',
        'urgently',
        'dobrodel',
    ];

    public function managements ()
    {
        return $this->hasMany( 'App\Models\TicketManagement' );
    }

    public function allowedManagements ()
    {
        $q = $this->hasMany( 'App\Models\TicketManagement' );
        if ( Auth::user()->management_id && ! Auth::user()->can( 'tickets.managements_all' ) )
        {
            $q->where( 'management_id', '=', Auth::user()->management_id );
        }
        return $q;
    }

    public function address ()
    {
        return $this->belongsTo( 'App\Models\Address' );
    }

    public function author ()
    {
        return $this->belongsTo( 'App\User' );
    }

    public function type ()
    {
        return $this->belongsTo( 'App\Models\Type' );
    }

    public function comments ()
    {
        return $this->hasMany( 'App\Models\Comment', 'model_id' )
			->where( 'model_name', '=', get_class( $this ) );
    }

    public function parent ()
    {
        return $this->belongsTo( 'App\Models\Ticket', 'parent_id' );
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
	
	public function tags ()
    {
        return $this->hasMany( 'App\Models\Tag', 'model_id' )
            ->where( 'model_name', '=', get_class( $this ) );
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

    public function scopeMine ( $query )
    {
        $user = Auth::user();
        return $query
            ->where( function ( $q ) use ( $user )
            {
                if ( $user->can( 'tickets.all' ) ) return;
                $q
                    ->whereIn( 'status_code', $user->getAvailableStatuses() );
                if ( $user->can( 'tickets.executor' ) && $user->management )
                {
                    $q
                        ->whereHas( 'allowedManagements', function ( $q2 ) use ( $user )
                        {
                            return $q2
                                ->where( 'management_id', '=', $user->management->id );
                        });
                }
                else
                {
                    $q
                        ->where( 'author_id', '=', Auth::user()->id );
                }
                return $q;
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

        $attributes['phone'] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes['phone'] ), -10 );
        if ( !empty( $attributes['phone2'] ) )
        {
            $attributes['phone2'] = mb_substr( preg_replace( '/[^0-9]/', '', $attributes['phone2'] ), -10 );
        }

        $ticket = new Ticket( $attributes );
        $ticket->author_id = Auth::user()->id;
        $ticket->place = self::$places[ $attributes['place'] ];

        $address = Address
            ::where( 'name', '=', trim( $ticket->address ) )
            ->first();

        if ( $address )
        {
            $ticket->address_id = $address->id;
            //$ticket->management_id = $address->management_id;
        }

        $ticket->save();

        return $ticket;

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
        $phone = '+7 (' . mb_substr( $this->phone, 0, 3 ) . ') ' . mb_substr( $this->phone, 3, 3 ) . '-' . mb_substr( $this->phone, 6, 2 ). '-' . mb_substr( $this->phone, 8, 2 );
        if ( $html )
        {
            $phones = '<a href="tel:7' . $this->phone . '" class="inherit">' . $phone . '</a';
        }
        else
        {
            $phones = $phone;
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
	    $user_statuses = Auth::user()->getAvailableStatuses();
		$workflow = self::$workflow[ $this->status_code ] ?? [];
		$statuses = [];
		foreach ( $workflow as $status_code )
		{
		    if ( in_array( $status_code, $user_statuses ) )
            {
                $statuses[ $status_code ] = self::$statuses[ $status_code ];
            }
		}
		return $statuses;
	}

	public function getColor ()
    {

        $now = Carbon::now();

        switch ( $this->status_code )
        {

            case 'accepted':

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

            case 'transferred_management':

                if ( $this->type->period_acceptance )
                {

                    $dt = Carbon::parse( $this->created_at );
                    $dt->addSeconds( $this->type->period_acceptance * 60 * 60 );

                    if ( $now->timestamp > $dt->timestamp )
                    {
                        return 'danger';
                    }

                }

                return 'warning';

                break;

            case 'not_verified':
            case 'cancel':
            case 'no_contract':
                return 'danger';
                break;

            case 'accepted_management':
                return 'success';
                break;

            case 'transferred_management_again':
                return 'warning';
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

    public function canEdit ()
    {
        if ( is_null( $this->can_edit ) )
        {
            if ( \Auth::user()->can( 'tickets.edit' ) && ( $this->status_code == 'draft' || $this->status_code == 'created' ) )
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

        if ( $this->group_uuid )
        {
            foreach ( $this->group as $ticket )
            {

                $ticket->status_code = $status_code;
                $ticket->status_name = self::$statuses[ $status_code ];
                $ticket->save();

                $res = StatusHistory::create([
                    'model_id'          => $ticket->id,
                    'model_name'        => get_class( $ticket ),
                    'status_code'       => $status_code,
                    'status_name'       => self::$statuses[ $status_code ],
                ]);
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

                $res = $ticket->processStatus();
                if ( $res instanceof MessageBag )
                {
                    return $res;
                }

            }
        }
        else
        {

            $this->status_code = $status_code;
            $this->status_name = self::$statuses[ $status_code ];
            $this->save();

            $res = StatusHistory::create([
                'model_id'          => $this->id,
                'model_name'        => get_class( $this ),
                'status_code'       => $status_code,
                'status_name'       => self::$statuses[ $status_code ],
            ]);
            if ( $res instanceof MessageBag )
            {
                return $res;
            }

            $res = $this->processStatus();
            if ( $res instanceof MessageBag )
            {
                return $res;
            }

        }

        \DB::commit();

    }

    public function processStatus ()
    {

        switch ( $this->status_code )
        {

            case 'cancel':
            case 'no_contract':

                foreach ( $this->managements as $management )
                {
					if ( $management->status_code != $this->status_code )
					{
						$res = $management->changeStatus( $this->status_code, true );
						if ( $res instanceof MessageBag )
						{
							return $res;
						}
					}
                }

                break;

            case 'transferred':
            case 'transferred_again':

                foreach ( $this->managements as $management )
                {
                    if ( $management->status_code != 'transferred' && $management->status_code != 'transferred_again' )
                    {
                        $res = $management->changeStatus( $this->status_code, true );
                        if ( $res instanceof MessageBag )
                        {
                            return $res;
                        }
                    }
                }

                break;

        }

    }

}
