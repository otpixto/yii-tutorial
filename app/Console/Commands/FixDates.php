<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;

class FixDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:dates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Корректировка дат';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct ()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle ()
    {
        $tickets = Ticket
            ::whereNull( 'transferred_at' )
            ->orWhereNull( 'accepted_at' )
            ->orWhereNull( 'completed_at' )
            ->orWhereNull( 'deadline_acceptance' )
            ->orWhereNull( 'deadline_execution' )
            ->orWhereNull( 'duration_work' )
            ->where( 'status_code', '!=', 'draft' )
            ->get();
        $bar = $this->output->createProgressBar( $tickets->count() );
        foreach ( $tickets as $ticket )
        {
            $status_transferred = $ticket->statusesHistory->whereIn( 'status_code', [ 'transferred', 'transferred_again' ] )->sortByDesc( 'id' )->first();
            if ( ! $status_transferred ) continue;
            $ticket->transferred_at = $status_transferred->created_at->format( 'Y-m-d H:i:s' );
            $ticket->deadline_acceptance = $status_transferred->created_at->addMinutes( $ticket->type->period_acceptance * 60 )->format( 'Y-m-d H:i:s' );
            $ticket->deadline_execution = $status_transferred->created_at->addMinutes( $ticket->type->period_execution * 60 )->format( 'Y-m-d H:i:s' );
            $status_accepted = $ticket->statusesHistory->where( 'status_code', 'accepted' )->sortByDesc( 'id' )->first();
            $status_completed = $ticket->statusesHistory->whereIn( 'status_code', [ 'completed_with_act', 'completed_without_act', 'not_verified' ] )->sortByDesc( 'id' )->first();
            if ( $status_accepted )
            {
                $ticket->accepted_at = $status_accepted->created_at->format( 'Y-m-d H:i:s' );
            }
            if ( $status_completed )
            {
                $ticket->completed_at = $status_completed->created_at->format( 'Y-m-d H:i:s' );
                $ticket->duration_work = number_format( $status_completed->created_at->diffInMinutes( $status_transferred->created_at ) / 60, 2, '.', '' );
            }
            $ticket->save();
            $bar->advance();
        }
        $bar->finish();
    }
}