<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;

class Ticket extends Model
{

    protected $table = 'tickets';
	
	public static $statuses = [
		'draft'					    => 'Черновик',
        'accepted_operator'         => 'Принято оператором ЕДС',
        'accepted_management'       => 'Принято к исполнению УК',
        'done'				    	=> 'Выполнено',
        'closed_success'		    => 'Закрыто с оценкой',
        'closed_without_confirm'	=> 'Закрыто без подтверждения',
        'not_confirmed'             => 'Проблема не потверждена',
        'not_done'                  => 'Не выполнено',
        'cancel'				    => 'Отмена',
        'failure'                   => 'Отказ',
	];
	
	public static $workflow = [
		'draft' => [ 
			'accepted_operator',
		],
		'accepted_operator' => [
			'accepted_management',
			'cancel',
            'failure',
		],
		'accepted_management' => [
			'done',
			'done_without_act',
            'not_confirmed',
            'not_done',
            'cancel',
		],
		'done' => [
			'closed_success',
			'closed_without_confirm',
            'not_done',
		],
        'done_without_act' => [
            'closed_success',
            'closed_without_confirm',
            'not_done',
        ],
        'not_confirmed' => [

        ],
        'not_done' => [

        ],
		'closed_success' => [ 
		
		],
		'closed_without_confirm' => [

		],
		'cancel' => [ 

		],
        'failure' => [

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
        'address'           => 'max:191'
    ];

    protected $fillable = [
        'type_id',
        'firstname',
        'middlename',
        'lastname',
        'phone',
        'phone2',
        'text',
        'address'
    ];

    public function managements ()
    {
        return $this->belongsToMany( 'App\Models\Management', 'tickets_managements' );
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

    public function group ()
    {
        return $this->hasMany( 'App\Models\Ticket', 'group_uuid', 'group_uuid' );
    }
	
	public function tags ()
    {
        return $this->hasMany( 'App\Models\Tag', 'model_id' )
            ->where( 'model_name', '=', get_class( $this ) );
    }

    public function scopeMine ( $query )
    {
        $user = Auth::user();
        return $query
            ->where( function ( $q ) use ( $user )
            {
                if ( $user->can( 'tickets.all' ) ) return true;
                if ( $user->can( 'tickets.executor' ) && $user->management )
                {
                    return $q
                        ->whereHas( 'managements', function ( $q2 ) use ( $user )
                        {
                            return $q2
                                ->where( 'managements.id', '=', $user->management->id );
                        });
                }
                return $q
                    ->where( 'author_id', '=', Auth::user()->id );
            });
    }

    public function scopeGroupped ( $query )
    {
        return $query
            ->groupBy( 'group_uuid' );
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

        $address = Address
            ::where( 'name', '=', trim( $ticket->address ) )
            ->first();
        if ( $address )
        {
            $ticket->address_id = $address->id;
            $ticket->management_id = $address->management_id;
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
		$workflow = self::$workflow[ $this->status ];
		$statuses = [];
		foreach ( $workflow as $wf )
		{
			$statuses[ $wf ] = self::$statuses[ $wf ];
		}
		return $statuses;
	}
	
	public function getStatusName ()
	{
		return self::$statuses[ $this->status ];
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

    public function changeStatus ( $status )
    {

        if ( ! isset( self::$statuses[ $status ] ) )
        {
            return new MessageBag([ 'Некорректный статус' ]);
        }

        if ( ! in_array( $status, self::$workflow[ $this->status ] ) )
        {
            return new MessageBag([ 'Невозможно сменить статус!' ]);
        }

        $this->status = $status;
        $this->save();

    }

}
