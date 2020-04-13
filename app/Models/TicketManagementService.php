<?php

namespace App\Models;

class TicketManagementService extends BaseModel
{

    protected $table = 'tickets_managements_services';
    public static $_table = 'tickets_managements_services';

    public static $name = 'Выполненные работы';

    protected $fillable = [
        'ticket_management_id',
        'name',
        'quantity',
        'unit',
        'amount',
    ];

    public function ticketManagement ()
    {
        return $this->belongsTo( TicketManagement::class );
    }

}
