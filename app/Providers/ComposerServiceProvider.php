<?php

namespace App\Providers;

use App\Models\TicketManagement;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot ()
    {
        View::composer( [ 'tickets.index', 'tickets.edit', 'tickets.show', 'works.index', 'works.edit', 'works.show' ], function ( $view )
        {
            if ( \Cache::has( 'tickets.scheduled.now' ) )
            {
                $ticketManagements = \Cache::get( 'tickets.scheduled.now' );
            }
            else
            {
                $now = Carbon::now()->toDateTimeString();
                $ticketManagements = TicketManagement
                    ::mine()
                    ->where( 'status_code', '=', 'assigned' )
                    ->where( 'scheduled_begin', '<=', $now )
                    ->get();
                \Cache::put( 'tickets.scheduled.now', $ticketManagements, 15 );
            }
            return $view
                ->with( 'scheduledTicketManagements', $ticketManagements );
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register ()
    {
        //
    }

}