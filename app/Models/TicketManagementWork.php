<?php

namespace App\Models;

class TicketManagementWork extends BaseModel
{

    protected $table = 'tickets_managements_works';
    public static $_table = 'tickets_managements_works';

    public static $name = 'Выполненные работы';

    protected $fillable = [
        'ticket_management_id',
        'name',
        'quantity',
    ];

    public function ticketManagement ()
    {
        return $this->belongsTo( 'App\Models\TicketManagement' );
    }

}
