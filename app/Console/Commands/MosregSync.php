<?php

namespace App\Console\Commands;

use App\Models\TicketManagement;
use App\User;
use Illuminate\Console\Command;

class MosregSync extends Command
{
    protected $signature = 'mosreg:sync';
    protected $description = 'Mosreg sync';
    const STATUSES = [
        'closed_with_confirm',
        'closed_without_confirm',
        'cancel',
        'GZI_SOLVED',
        'rejected',
        'rejected_operator',
    ];
    public function handle ()
    {
        try
        {
            $user = User::find( config( 'gzhi.user_id' ) );
            if ( ! $user )
            {
                throw new \Exception( 'User not found' );
            }
            \Auth::login( $user );
            $ticketManagements = TicketManagement
                ::whereNotNull( 'mosreg_id' )
                ->whereNotIn( 'status_code', self::STATUSES )
                ->where( 'mosreg_status', '!=', 'SOLVED' )
                ->get();
            foreach ( $ticketManagements as $ticketManagement )
            {
                if ( $ticketManagement->management && $ticketManagement->management->hasMosreg( $mosregClient ) )
                {
                    $mosregTicket = $mosregClient->getTicket( $ticketManagement->mosreg_id );
                    if ( $mosregTicket && $mosregTicket->status_code != $ticketManagement->mosreg_status )
                    {
                        $ticketManagement->changeMosregStatus( $mosregTicket->status_code );
                    }
                }
            }
        }
        catch ( \Exception $e )
        {
            dd( $e );
        }
    }
}