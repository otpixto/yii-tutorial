<?php

namespace App\Models;

class TicketManagementWork extends BaseModel
{

    protected $table = 'tickets_managements_works';

    public static $name = 'Выполненные работы';

    protected $fillable = [
        'ticket_management_id',
        'name',
        'quantity',
    ];
	
	public static $rules = [
        'ticket_management_id'	    => 'required|integer',
        'name'				        => 'required|string',
        'quantity'				    => 'required|integer|min:1',
    ];

    public function ticketManagement ()
    {
        return $this->belongsTo( 'App\Models\TicketManagement' );
    }

}
