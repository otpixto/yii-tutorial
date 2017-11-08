<?php

namespace App\Http\Controllers\Operator;

use App\Models\Ticket;
use App\Models\Management;

class DataController extends BaseController
{

    public function __construct ()
    {
        parent::__construct();
    }

    public function addresses ()
    {

		$res = Ticket
			::whereHas( 'managements', function ( $q )
			{
				return $q
					->whereIn( 'management_id', Management::mine()->pluck( 'id' ) );
			})
			->whereDoesntHave( 'address', function ( $q )
			{
				return $q
					->where( 'lon', '=', -1 )
					->orWhere( 'lat', '=', -1 );
			})
			->get();

		$data = [];
		foreach ( $res as $r )
		{
			if ( ! isset( $data[ $r->address_id ] ) )
			{
				if ( ! $r->address->lon || ! $r->address->lat )
				{
					$yandex = json_decode( file_get_contents( 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' . urldecode( $r->address->name ) ) );
					if ( isset( $yandex->response->GeoObjectCollection->featureMember[0] ) )
					{
						$pos = explode( ' ', $yandex->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos );
						$r->address->lon = $pos[0];
						$r->address->lat = $pos[1];
					}
					else
					{
						$r->address->lon = -1;
						$r->address->lat = -1;
					}
					$r->address->save();
				}
				$managements = [];
				foreach ( $r->managements as $m )
				{
					$managements[] = $m->management->name;
				}
				$data[ $r->address_id ] = [ $r->address_id, $r->address->name, [ $r->address->lat, $r->address->lon ], $managements, 1 ];
			}
			else
			{
				$data[ $r->address_id ][ 4 ] ++;
			}
		}
		
		return array_values( $data );

    }

}