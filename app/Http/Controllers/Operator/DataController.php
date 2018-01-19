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

    public function addresses ()
    {

		$res = Ticket
			::mine()
			->whereDoesntHave( 'address', function ( $q )
			{
				return $q
					->where( 'lon', '=', -1 )
					->orWhere( 'lat', '=', -1 );
			})
			->with( 'address', 'managements' )
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
				foreach ( $r->managements()->whereIn( 'management_id', Management::mine()->pluck( 'id' ) )->get() as $m )
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

    public function worksAddresses ()
    {

        $res = Work
            ::mine()
            ->current()
            ->whereDoesntHave( 'addresses', function ( $q )
            {
                return $q
                    ->where( 'lon', '=', -1 )
                    ->orWhere( 'lat', '=', -1 );
            })
            ->with( 'addresses' )
            ->get();

        $data = [];
        foreach ( $res as $r )
        {
            foreach ( $r->addresses as $address )
            {
                if ( ! isset( $data[ $address->id ] ) )
                {
                    if ( ! $address->lon || ! $address->lat )
                    {
                        $yandex = json_decode( file_get_contents( 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' . urldecode( $address->name ) ) );
                        if ( isset( $yandex->response->GeoObjectCollection->featureMember[0] ) )
                        {
                            $pos = explode( ' ', $yandex->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos );
                            $address->lon = $pos[0];
                            $address->lat = $pos[1];
                        }
                        else
                        {
                            $address->lon = -1;
                            $address->lat = -1;
                        }
                        $address->save();
                    }
                    $data[ $address->id ] = [ $address->id, $address->name, [ $address->lat, $address->lon ], 1 ];
                }
                else
                {
                    $data[ $address->id ][ 3 ] ++;
                }
            }
        }

        return array_values( $data );

    }

}