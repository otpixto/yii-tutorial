<?php

namespace App\Classes;

use App\Models\Building;
use App\Models\Ticket;
use App\Models\Work;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LK
{

    public static function ticketsInfo ( LengthAwarePaginator $tickets, $withDetails = false ) : array
    {
        $per_page = $tickets->perPage();
        $page = $tickets->currentPage();
        $total = $tickets->total();
        $pages = ceil( $total / $per_page );
        $response = [
            'tickets' => [],
            'per_page' => $per_page,
            'page' => $page,
            'pages' => $pages,
            'total' => $total
        ];
        foreach ( $tickets as $ticket )
        {
            $response[ 'tickets' ][] = self::ticketInfo( $ticket, $withDetails );
        }
        return $response;
    }

    public static function ticketInfo ( Ticket $ticket, $withDetails = false ) : array
    {
        $info = [
            'id'            => (int) $ticket->id,
            'create_date'   => (int) $ticket->created_at->timestamp,
            'type_id'       => (int) $ticket->type_id,
            'type_name'     => $ticket->type->name,
            'building_id'   => (int) $ticket->building_id,
            'building_name' => $ticket->building->name,
            'flat'          => $ticket->flat,
            'status_code'   => $ticket->status_code,
            'status_name'   => $ticket->status_name,
            'lon'           => ( (float) $ticket->building->lon ) ?: null,
            'lat'           => ( (float) $ticket->building->lat ) ?: null,
            'need_act'      => (bool) $ticket->needAct(),
            'text'          => $ticket->text,
            'time_from'     => Carbon::parse( $ticket->time_from )->format( 'H:i' ),
            'time_to'       => Carbon::parse( $ticket->time_to )->format( 'H:i' ),
        ];
        if ( $withDetails )
        {
            $info[ 'managements' ] = [];
            $info[ 'history' ] = [];
            foreach ( $ticket->managements as $ticketManagement )
            {
                $management = [
                    'management_name'   => $ticketManagement->management->name,
                    'executor_name'     => null,
                    'scheduled_begin'   => null,
                    'scheduled_end'     => null,
                ];
                if ( $ticketManagement->executor )
                {
                    $management[ 'executor_name' ] = $ticketManagement->executor->name;
                    $management[ 'scheduled_begin' ] = $ticketManagement->scheduled_begin->timestamp ?? null;
                    $management[ 'scheduled_end' ] = $ticketManagement->scheduled_end->timestamp ?? null;
                }
                $info[ 'managements' ][] = $management;
            }
            foreach ( $ticket->statusesHistory as $statusHistory )
            {
                $info[ 'history' ][] = [
                    'author'        => $statusHistory->author->getName(),
                    'datetime'      => $statusHistory->created_at->timestamp,
                    'status_code'   => $statusHistory->status_code,
                    'status_name'   => $statusHistory->status_name,
                ];
            }
        }
        return $info;
    }

    public static function worksInfo ( LengthAwarePaginator $works ) : array
    {
        $per_page = $works->perPage();
        $page = $works->currentPage();
        $total = $works->total();
        $pages = ceil( $total / $per_page );
        $response = [
            'works' => [],
            'per_page' => $per_page,
            'page' => $page,
            'pages' => $pages,
            'total' => $total
        ];
        foreach ( $works as $work )
        {
            $response[ 'works' ][] = self::workInfo( $work );
        }
        return $response;
    }

    public static function workInfo ( Work $work ) : array
    {
        $info = [
            'id'                => (int) $work->id,
            'create_date'       => (int) $work->created_at->timestamp,
            'type'              => Work::$types[ $work->type_id ],
            'category'          => $work->category->name,
            'composition'       => $work->composition,
            'reason'            => $work->reason,
            'time_begin'        => (int) ( $work->time_begin->timestamp ?? 0 ),
            'time_end'          => (int) ( $work->time_end->timestamp ?? 0 ),
            'time_end_fact'     => (int) ( $work->time_end_fact->timestamp ?? 0 ),
            'managements'       => [],
            'buildings'         => [],
        ];
        foreach ( $work->managements as $management )
        {
            $info[ 'managements' ][] = $management->name;
        }
        foreach ( $work->buildings as $building )
        {
            $info[ 'buildings' ][] = [
                'building_id' => $building->id,
                'building_name' => $building->name,
                'coors' => [
                    (float) $building->lat,
                    (float) $building->lon
                ],
            ];
        }
        return $info;
    }

    public static function buildingsInfo ( Collection $buildings ) : array
    {
        $response = [];
        foreach ( $buildings as $building )
        {
            $response[] = self::buildingInfo( $building );
        }
        return $response;
    }

    public static function buildingInfo ( Building $building ) : array
    {
        $info = [
            'id'                    => (int) $building->id,
            'text'                  => $building->name,
            'lon'                   => ( (float) $building->lon ) ?: null,
            'lat'                   => ( (float) $building->lat ) ?: null,
            'room_total_count'      => ( (int) $building->room_total_count ) ?: null,
        ];
        return $info;
    }

}