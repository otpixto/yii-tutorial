<?php

namespace App\Models;

class Executor extends BaseModel
{

    protected $table = 'managements_executors';
    public static $_table = 'managements_executors';

    public static $name = 'Исполнитель';

    protected $fillable = [
        'management_id',
        'name',
        'phone',
    ];

    protected $nullable = [
        'phone',
    ];
	
	public static $rules = [
        'management_id'	    => 'required|integer',
        'name'				=> 'required|string',
        'phone'				=> 'nullable|string',
    ];

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    public function managements ()
    {
        return $this->hasMany( 'App\Models\Management', 'id', 'executor_id' );
    }

    public function tickets ()
    {
        return $this->hasMany( 'App\Models\TicketManagement' );
    }

    public function works ()
    {
        return $this->hasMany( 'App\Models\Work' );
    }

    public function scopeMine ( $query )
    {
        return $query
            ->whereHas( 'management', function ( $management )
            {
                return $management
                    ->mine();
            });
    }

    public function getPhone ( $html = false )
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
        return $phones;
    }

}
