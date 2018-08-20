<?php

namespace App\Models;

class ManagementAct extends BaseModel
{

    protected $table = 'managements_acts';
    public static $_table = 'managements_acts';

    public static $name = 'Акты';

    public static $rules = [
        'management_id'         => 'required|integer',
        'name'                  => 'required',
        'content'               => 'required',
    ];

    protected $fillable = [
        'management_id',
        'name',
        'content',
    ];

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    public function getPreparedContent ( TicketManagement $ticketManagement )
    {
        $ticket = $ticketManagement->ticket;
        $management = $ticketManagement->management->name;
        if ( $ticketManagement->management->parent )
        {
            $management = $ticketManagement->management->parent->name . '<br />' . $management;
        }
        $content = $this->content;
        $content = str_replace( '[[object]]', $management, $content );
        $content = str_replace( '[[emergency]]', $ticket->emergency ? 'Авария' : '', $content );
        $content = str_replace( '[[urgent]]', $ticket->urgently ? 'Срочно' : '', $content );
        $content = str_replace( '[[urgent_details]]', '', $content );
        $content = str_replace( '[[number]]', $ticket->id, $content );
        $content = str_replace( '[[client]]', $ticket->getName(), $content );
        $content = str_replace( '[[phone]]', $ticket->getPhones(), $content );
        $content = str_replace( '[[address]]', $ticket->getAddress(), $content );
        $content = str_replace( '[[executor_name]]', $ticketManagement->executor->name ?? '', $content );
        $content = str_replace( '[[problem]]', $ticket->type->name, $content );
        $content = str_replace( '[[details]]', $ticket->text, $content );
        $content = str_replace( '[[execution_date]]', $ticket->scheduled_begin ? $ticket->scheduled_begin->format( 'd.m.Y' ) : '', $content );
        $content = str_replace( '[[execution_time]]', $ticket->scheduled_begin ? $ticket->scheduled_begin->format( 'H:i' ) : '', $content );
        $content = str_replace( '[[additional_phones]]', '', $content );
        $content = str_replace( '[[porch]]', '', $content );
        $content = str_replace( '[[floor]]', '', $content );
        $content = str_replace( '[[flat]]', '', $content );
        $content = str_replace( '[[executor_position]]', '', $content );
        $content = str_replace( '[[services]]', view( 'catalog.managements.parts.act_services' )->with( 'services', $ticketManagement->services )->render(), $content );
        return $content;
    }

}
