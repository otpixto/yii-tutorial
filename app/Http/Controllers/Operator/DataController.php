<?php

namespace App\Http\Controllers\Operator;

use App\Models\Ticket;
use App\Models\Management;
use App\Models\Work;

class DataController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
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
                    ->where( 'lon', '!=', - 1 )
                    ->where( 'lat', '!=', - 1 );
            })
            ->with(
                'building',
                'managements',
                'managements.management'
            )
            ->get();

        $data = [];
        foreach ( $res as $r )
        {
            if ( ! isset( $data[ $r->building_id ] ) )
            {
                if ( ! $r->building->lon || ! $r->building->lat )
                {
                    $yandex = json_decode( file_get_contents( 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' . urldecode( $r->building->name ) ) );
                    if ( isset( $yandex->response->GeoObjectCollection->featureMember[ 0 ] ) )
                    {
                        $pos = explode( ' ', $yandex->response->GeoObjectCollection->featureMember[ 0 ]->GeoObject->Point->pos );
                        $r->building->lon = $pos[ 0 ];
                        $r->building->lat = $pos[ 1 ];
                    } else
                    {
                        $r->building->lon = - 1;
                        $r->building->lat = - 1;
                    }
                    $r->building->save();
                }
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

    public function worksBuildings ()
    {

        $res = Work
            ::mine()
            ->current()
            ->whereHas( 'buildings', function ( $buildings )
            {
                return $buildings
                    ->where( 'lon', '!=', - 1 )
                    ->where( 'lat', '!=', - 1 );
            })
            ->with( 'buildings' )
            ->get();

        $data = [];
        foreach ( $res as $r )
        {
            foreach ( $r->buildings as $building )
            {
                if ( ! isset( $data[ $building->id ] ) )
                {
                    if ( ! $building->lon || ! $building->lat )
                    {
                        $yandex = json_decode( file_get_contents( 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' . urldecode( $building->name ) ) );
                        if ( isset( $yandex->response->GeoObjectCollection->featureMember[0] ) )
                        {
                            $pos = explode( ' ', $yandex->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos );
                            $building->lon = $pos[0];
                            $building->lat = $pos[1];
                        }
                        else
                        {
                            $building->lon = -1;
                            $building->lat = -1;
                        }
                        $building->save();
                    }
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
                $management = $r->management->name;
                if ( $r->management->parent )
                {
                    $management = $r->management->parent->name . ' ' . $management;
                }
                $executor = $r->executor ? $r->executor->name : null;
                $data[ $building->id ][ 'works' ][] = [
                    'id'                => $r->id,
                    'url'               => route( 'works.show', $r->id ),
                    'management'        => $management,
                    'executor'          => $executor,
                    'composition'       => $r->composition,
                    'category'          => $r->category->name,
                    'time_end'          => $r->time_end,
                ];
            }
        }

        return array_values( $data );

    }

}