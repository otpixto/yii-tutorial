<?php

namespace App\Http\Controllers\Operator;

use App\Models\BuildingRoom;
use App\Models\Ticket;
use App\Models\Work;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DataController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function positions ( Request $request )
    {
        $users = User
            ::mine()
            ->whereHas( 'executors', function ( $executors )
            {
                return $executors
                    ->mine();
            });
        if ( $request->get( 'user_id' ) )
        {
            $users
                ->where( 'id', '=', $request->get( 'user_id' ) );
        }
        if ( $request->get( 'history' ) )
        {
            $users
                ->whereHas( 'positions', function ( $positions ) use ( $request )
                {
                    $positions
                        ->whereNotNull( 'lon' )
                        ->whereNotNull( 'lat' )
                        ->whereNotNull( 'position_at' );
                    if ( $request->get( 'date_from' ) )
                    {
                        $positions
                            ->where( 'position_at', '>=', Carbon::parse( $request->get( 'date_from' ) )->toDateTimeString() );
                    }
                    if ( $request->get( 'date_to' ) )
                    {
                        $positions
                            ->where( 'position_at', '<=', Carbon::parse( $request->get( 'date_to' ) )->toDateTimeString() );
                    }
                    return $positions;
                });
        }
        else
        {
            $users
                ->whereNotNull( 'lon' )
                ->whereNotNull( 'lat' )
                ->whereNotNull( 'position_at' );
            if ( $request->get( 'date_from' ) )
            {
                $users
                    ->where( 'position_at', '>=', Carbon::parse( $request->get( 'date_from' ) )->toDateTimeString() );
            }
            if ( $request->get( 'date_to' ) )
            {
                $users
                    ->where( 'position_at', '<=', Carbon::parse( $request->get( 'date_to' ) )->toDateTimeString() );
            }
        }
        $users = $users
            ->get();
        $data = [];
        foreach ( $users as $user )
        {
            $history = [];
            if ( $request->get( 'history' ) )
            {
                $positions = $user->positions()
                    ->whereNotNull( 'lon' )
                    ->whereNotNull( 'lat' )
                    ->whereNotNull( 'position_at' );
                if ( $request->get( 'date_from' ) )
                {
                    $positions
                        ->where( 'position_at', '>=', Carbon::parse( $request->get( 'date_from' ) )->toDateTimeString() );
                }
                if ( $request->get( 'date_to' ) )
                {
                    $positions
                        ->where( 'position_at', '<=', Carbon::parse( $request->get( 'date_to' ) )->toDateTimeString() );
                }
                $positions = $positions->get();
                foreach ( $positions as $position )
                {
                    $history[] = [
                        'lon' => (float) $position->lon,
                        'lat' => (float) $position->lat,
                        'date' => $position->position_at->format( 'd.m.Y H:i' ),
                    ];
                }
            }
            $data[] = [
                'user_id' => $user->id,
                'user_name' => $user->getShortName(),
                'lon' => (float) $user->lon,
                'lat' => (float) $user->lat,
                'date' => $user->position_at->format( 'd.m.Y H:i' ),
                'history' => $history
            ];
        }
        return $data;
    }

    public function buildings ()
    {

        $res = Ticket
            ::whereHas( 'managements', function ( $managements )
			{
				return $managements
					->mine();
			})
            ->notFinaleStatuses()
            ->whereHas( 'building', function ( $building )
            {
                return $building
                    ->whereNotNull( 'lon' )
                    ->whereNotNull( 'lat' )
                    ->where( 'lon', '!=', -1 )
                    ->where( 'lat', '!=', -1 );
            })
            ->where( 'created_at', '>=', Carbon::now()->subMonth()->toDateTimeString() )
            ->with(
                'building',
                'managements',
                'managements.management'
            )
            ->orderBy( 'id', 'desc' )
            ->get();

        $data = [];
        foreach ( $res as $r )
        {
            if ( ! isset( $data[ $r->building_id ] ) )
            {
                $data[ $r->building_id ] = [
                    'building_id'           => $r->building_id,
                    'building_name'         => $r->building->name,
                    'coors' => [
                        (float) $r->building->lat,
                        (float) $r->building->lon
                    ],
                    'tickets' => []
                ];
            }
            foreach ( $r->managements as $ticketManagement )
            {
                $data[ $r->building_id ][ 'tickets' ][] = [
                    'number'        => $ticketManagement->getTicketNumber(),
                    'url'           => route( 'tickets.show', $ticketManagement->getTicketNumber() ),
                    'type'          => $r->type->name,
                    'management'    => $ticketManagement->management->name,
                    'text'          => $r->text
                ];
            }
        }

        return array_values( $data );

    }

    public function buildingsRooms ( Request $request, $id )
    {

        $rooms = BuildingRoom::where( 'building_id', '=', $id )->get();
        return $rooms;

    }

    public function worksBuildings ( Request $request )
    {

        $res = Work
            ::mine()
            ->current()
            ->whereHas( 'building', function ( $building )
            {
                return $building
                    ->whereNotNull( 'lon' )
                    ->whereNotNull( 'lat' )
                    ->where( 'lon', '!=', -1 )
                    ->where( 'lat', '!=', -1 );
            });

        if ( $request->get( 'category_id' ) )
        {
            $res
                ->where( 'category_id', '=', $request->get( 'category_id' ) );
        }

        $res = $res->get();

        $data = [];
        foreach ( $res as $r )
        {
            foreach ( $r->buildings as $building )
            {
                if ( ! isset( $data[ $building->id ] ) )
                {
                    $data[ $building->id ] = [
                        'building_id' => $building->id,
                        'building_name' => $building->name,
                        'coors' => [
                            (float) $building->lat,
                            (float) $building->lon
                        ],
                        'works' => []
                    ];
                }
                $managements = $r->managements()->mine()->get()->implode( 'name', '; ' );
                $executors = $r->executors()->mine()->get()->implode( 'name', '; ' );
                $data[ $building->id ][ 'works' ][] = [
                    'id'                => $r->id,
                    'url'               => route( 'works.show', $r->id ),
                    'management'        => $managements,
                    'executor'          => $executors,
                    'composition'       => $r->composition,
                    'category'          => $r->category->name,
                    'time_end'          => $r->time_end->format( 'd.m.Y H:i' ),
                ];
            }
        }

        return array_values( $data );

    }

}
