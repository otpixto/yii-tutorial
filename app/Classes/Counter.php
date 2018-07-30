<?php

namespace App\Classes;

use App\Models\TicketManagement;
use App\Models\Work;

class Counter
{

    private static $cache_life = 30; // minutes

    private static $tickets_count = null;
    private static $tickets_overdue_count = null;
    private static $tickets_not_processed_count = null;
    private static $tickets_in_process_count = null;
    private static $tickets_completed_count = null;

    private static $works_count = null;
    private static $works_overdue_count = null;

    public static function ticketsCount ()
    {
        if ( is_null( self::$tickets_count ) )
        {
            if ( ! \Cache::tags( 'tickets_counts' )->has( 'user.' . \Auth::user()->id . '.tickets_count' ) )
            {
                self::$tickets_count = TicketManagement
                    ::mine()
                    ->notFinaleStatuses()
                    ->count();
                \Cache::tags( 'tickets_counts' )->put( 'user.' . \Auth::user()->id . '.tickets_count', self::$tickets_count, self::$cache_life );
            }
            else
            {
                self::$tickets_count = \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_count' );
            }
        }
        return self::$tickets_count;
    }

    public static function ticketsOverdueCount ()
    {
        if ( is_null( self::$tickets_overdue_count ) )
        {
            if ( ! \Cache::tags( 'tickets_counts' )->has( 'user.' . \Auth::user()->id . '.tickets_overdue_count' ) )
            {
                self::$tickets_overdue_count = TicketManagement
                    ::mine()
                    ->whereHas( 'ticket', function ( $ticket )
                    {
                        return $ticket
                            ->notFinaleStatuses()
                            ->overdue();
                    })
                    ->count();
                \Cache::tags( 'tickets_counts' )->put( 'user.' . \Auth::user()->id . '.tickets_overdue_count', self::$tickets_overdue_count, self::$cache_life );
            }
            else
            {
                self::$tickets_overdue_count = \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_overdue_count' );
            }
        }
        return self::$tickets_overdue_count;
    }

    public static function ticketsNotProcessedCount ()
    {
        if ( is_null( self::$tickets_not_processed_count ) )
        {
            if ( ! \Cache::tags( 'tickets_counts' )->has( 'user.' . \Auth::user()->id . '.tickets_not_processed_count' ) )
            {
                self::$tickets_not_processed_count = TicketManagement
                    ::mine()
                    ->notProcessed()
                    ->count();
                \Cache::tags( 'tickets_counts' )->put( 'user.' . \Auth::user()->id . '.tickets_not_processed_count', self::$tickets_not_processed_count, self::$cache_life );
            }
            else
            {
                self::$tickets_not_processed_count = \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_not_processed_count' );
            }
        }
        return self::$tickets_not_processed_count;
    }

    public static function ticketsInProcessCount ()
    {
        if ( is_null( self::$tickets_in_process_count ) )
        {
            if ( ! \Cache::tags( 'tickets_counts' )->has( 'user.' . \Auth::user()->id . '.tickets_in_process_count' ) )
            {
                self::$tickets_in_process_count = TicketManagement
                    ::mine()
                    ->inProcess()
                    ->count();
                \Cache::tags( 'tickets_counts' )->put( 'user.' . \Auth::user()->id . '.tickets_in_process_count', self::$tickets_in_process_count, self::$cache_life );
            }
            else
            {
                self::$tickets_in_process_count = \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_in_process_count' );
            }
        }
        return self::$tickets_in_process_count;
    }

    public static function ticketsCompletedCount ()
    {
        if ( is_null( self::$tickets_completed_count ) )
        {
            if ( ! \Cache::tags( 'tickets_counts' )->has( 'user.' . \Auth::user()->id . '.tickets_completed_count' ) )
            {
                self::$tickets_completed_count = TicketManagement
                    ::mine()
                    ->completed()
                    ->count();
                \Cache::tags( 'tickets_counts' )->put( 'user.' . \Auth::user()->id . '.tickets_completed_count', self::$tickets_completed_count, self::$cache_life );
            }
            else
            {
                self::$tickets_completed_count = \Cache::tags( 'tickets_counts' )->get( 'user.' . \Auth::user()->id . '.tickets_completed_count' );
            }
        }
        return self::$tickets_completed_count;
    }

    public static function worksCount ()
    {
        if ( is_null( self::$works_count ) )
        {
            if ( ! \Cache::tags( 'works_counts' )->has( 'user.' . \Auth::user()->id . '.works_count' ) )
            {
                self::$works_count = Work
                    ::mine()
                    ->current()
                    ->count();
                \Cache::tags( 'works_counts' )->put( 'user.' . \Auth::user()->id . '.works_count', self::$works_count, self::$cache_life );
            }
            else
            {
                self::$works_count = \Cache::tags( 'works_counts' )->get( 'user.' . \Auth::user()->id . '.works_count' );
            }
        }
        return self::$works_count;
    }

    public static function worksOverdueCount ()
    {
        if ( is_null( self::$works_overdue_count ) )
        {
            if ( ! \Cache::tags( 'works_counts' )->has( 'user.' . \Auth::user()->id . '.works_overdue_count' ) )
            {
                self::$works_overdue_count = Work
                    ::mine()
                    ->current()
                    ->overdue()
                    ->count();
                \Cache::tags( 'works_counts' )->put( 'user.' . \Auth::user()->id . '.works_overdue_count', self::$works_overdue_count, self::$cache_life );
            }
            else
            {
                self::$works_overdue_count = \Cache::tags( 'works_counts' )->get( 'user.' . \Auth::user()->id . '.works_overdue_count' );
            }
        }
        return self::$works_overdue_count;
    }

}