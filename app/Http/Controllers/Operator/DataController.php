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
		
		if ( ! \Input::get( 'flush', 0 ) && \Cache::has( 'reports.map' ) )
		{
			$data = \Cache::get( 'reports.map' );
		}
		else
		{

			$res = Ticket
				::whereHas( 'managements', function ( $q )
				{
					return $q
						->whereIn( 'management_id', Management::mine()->pluck( 'id' ) );
				})
				->get();

			$data = [];
			foreach ( $res as $r )
			{
				if ( ! isset( $data[ $r->address_id ] ) )
				{
					$yandex = json_decode( file_get_contents( 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' . urldecode( $r->address->name ) ) );
					if ( ! isset( $yandex->response->GeoObjectCollection->featureMember[0] ) ) continue;
					$managements = [];
					foreach ( $r->managements as $m )
					{
						$managements[] = $m->management->name;
					}
					$data[ $r->address_id ] = [ $r->address_id, $r->address->name, $yandex->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos, $managements, 1 ];
				}
				else
				{
					$data[ $r->address_id ][ 4 ] ++;
				}
			}

			$data = array_values( $data );
			
			\Cache::put( 'reports.map', $data, 120 );
			
		}
		
		return $data;

    }

}