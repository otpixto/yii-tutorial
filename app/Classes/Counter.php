<?php

namespace App\Classes;

use App\Models\Provider;
use App\Models\TicketManagement;
use App\Models\Work;

class Counter
{

    private static $cache_life = 60; // minutes

    private static $tickets_count = null;
    private static $tickets_overdue_count = null;
    private static $tickets_not_processed_count = null;
    private static $tickets_in_process_count = null;
    private static $tickets_completed_count = null;
    private static $tickets_created_count = null;
    private static $tickets_rejected_count = null;
    private static $tickets_from_lk_count = null;
    private static $tickets_conflict_count = null;
    private static $tickets_confirmation_operator_count = null;
    private static $tickets_confirmation_client_count = null;

    private static $statuses = [];

    private static $works_count = null;
    private static $works_overdue_count = null;

    public static function ticketsCount ()
    {
        if ( is_null( self::$tickets_count ) )
        {
            if ( ! \Cache::tags( 'tickets_counts' )->has( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_count' ) )
            {
                self::$tickets_count = TicketManagement
                    ::mine()
                    ->notFinaleStatuses()
                    ->count();
                \Cache::tags( 'tickets_counts' )->put( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_count', self::$tickets_count, self::$cache_life );
            }
            else
            {
                self::$tickets_count = \Cache::tags( 'tickets_counts' )->get( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_count' );
            }
        }
        return self::$tickets_count;
    }

    public static function ticketsOverdueCount ()
    {
        if ( is_null( self::$tickets_overdue_count ) )
        {
            if ( ! \Cache::tags( 'tickets_counts' )->has( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_overdue_count' ) )
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
                \Cache::tags( 'tickets_counts' )->put( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_overdue_count', self::$tickets_overdue_count, self::$cache_life );
            }
            else
            {
                self::$tickets_overdue_count = \Cache::tags( 'tickets_counts' )->get( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_overdue_count' );
            }
        }
        return self::$tickets_overdue_count;
    }

    public static function ticketsNotProcessedCount ()
    {
        if ( is_null( self::$tickets_not_processed_count ) )
        {
            if ( ! \Cache::tags( 'tickets_counts' )->has( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_not_processed_count' ) )
            {
                self::$tickets_not_processed_count = TicketManagement
                    ::mine()
                    ->notProcessed()
                    ->count();
                \Cache::tags( 'tickets_counts' )->put( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_not_processed_count', self::$tickets_not_processed_count, self::$cache_life );
            }
            else
            {
                self::$tickets_not_processed_count = \Cache::tags( 'tickets_counts' )->get( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_not_processed_count' );
            }
        }
        return self::$tickets_not_processed_count;
    }

    public static function ticketsInProcessCount ()
    {
        if ( is_null( self::$tickets_in_process_count ) )
        {
            if ( ! \Cache::tags( 'tickets_counts' )->has( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_in_process_count' ) )
            {
                self::$tickets_in_process_count = TicketManagement
                    ::mine()
                    ->inProcess()
                    ->count();
                \Cache::tags( 'tickets_counts' )->put( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_in_process_count', self::$tickets_in_process_count, self::$cache_life );
            }
            else
            {
                self::$tickets_in_process_count = \Cache::tags( 'tickets_counts' )->get( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_in_process_count' );
            }
        }
        return self::$tickets_in_process_count;
    }

    public static function ticketsCompletedCount ()
    {
        if ( is_null( self::$tickets_completed_count ) )
        {
            if ( ! \Cache::tags( 'tickets_counts' )->has( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_completed_count' ) )
            {
                self::$tickets_completed_count = TicketManagement
                    ::mine()
                    ->completed()
                    ->count();
                \Cache::tags( 'tickets_counts' )->put( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_completed_count', self::$tickets_completed_count, self::$cache_life );
            }
            else
            {
                self::$tickets_completed_count = \Cache::tags( 'tickets_counts' )->get( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_completed_count' );
            }
        }
        return self::$tickets_completed_count;
    }

    public static function ticketsCountByStatus ( $status_code, $owner = false )
    {
        $key = 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets.' . $status_code . ( $owner ? '1' : '0' );
        if ( ! \Cache::tags( 'tickets_counts' )->has( $key ) )
        {
            $count = TicketManagement
                ::mine( $owner ? TicketManagement::I_AM_OWNER : TicketManagement::NOTHING )
                ->where( TicketManagement::$_table . '.status_code', '=', $status_code )
                ->count();
            \Cache::tags( 'tickets_counts' )->put( $key, $count, self::$cache_life );
        }
        else
        {
            $count = \Cache::tags( 'tickets_counts' )->get( $key );
        }
        return $count;
    }

    public static function ticketsCreatedCount ()
    {
        if ( is_null( self::$tickets_created_count ) )
        {
            if ( ! \Cache::tags( 'tickets_counts' )->has( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_created_count' ) )
            {
                self::$tickets_created_count = TicketManagement
                    ::mine()
                    ->where( TicketManagement::$_table . '.status_code', '=', 'created' )
                    ->count();
                \Cache::tags( 'tickets_counts' )->put( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_created_count', self::$tickets_created_count, self::$cache_life );
            }
            else
            {
                self::$tickets_created_count = \Cache::tags( 'tickets_counts' )->get( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_created_count' );
            }
        }
        return self::$tickets_created_count;
    }

    public static function ticketsConflictCount ()
    {
        if ( is_null( self::$tickets_conflict_count ) )
        {
            if ( ! \Cache::tags( 'tickets_counts' )->has( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_conflict_count' ) )
            {
                self::$tickets_conflict_count = TicketManagement
                    ::mine()
                    ->where( TicketManagement::$_table . '.status_code', '=', 'conflict' )
                    ->count();
                \Cache::tags( 'tickets_counts' )->put( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_conflict_count', self::$tickets_conflict_count, self::$cache_life );
            }
            else
            {
                self::$tickets_conflict_count = \Cache::tags( 'tickets_counts' )->get( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.tickets_conflict_count' );
            }
        }
        return self::$tickets_conflict_count;
    }

    public static function worksCount ()
    {
        if ( is_null( self::$works_count ) )
        {
            if ( ! \Cache::tags( 'works_counts' )->has( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.works_count' ) )
            {
                self::$works_count = Work
                    ::mine()
                    ->current()
                    ->count();
                \Cache::tags( 'works_counts' )->put( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.works_count', self::$works_count, self::$cache_life );
            }
            else
            {
                self::$works_count = \Cache::tags( 'works_counts' )->get( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.works_count' );
            }
        }
        return self::$works_count;
    }

    public static function worksOverdueCount ()
    {
        if ( is_null( self::$works_overdue_count ) )
        {
            if ( ! \Cache::tags( 'works_counts' )->has( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.works_overdue_count' ) )
            {
                self::$works_overdue_count = Work
                    ::mine()
                    ->current()
                    ->overdue()
                    ->count();
                \Cache::tags( 'works_counts' )->put( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.works_overdue_count', self::$works_overdue_count, self::$cache_life );
            }
            else
            {
                self::$works_overdue_count = \Cache::tags( 'works_counts' )->get( 'domain.' . Provider::getSubDomain() . '.user.' . \Auth::user()->id . '.works_overdue_count' );
            }
        }
        return self::$works_overdue_count;
    }

}