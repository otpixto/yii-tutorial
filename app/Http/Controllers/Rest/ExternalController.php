<?php

namespace App\Http\Controllers\Rest;

use App\Classes\SegmentChilds;
use App\Models\Segment;
use App\Models\Work;
use App\Models\Ticket;
use App\Models\TicketManagement;
use App\Models\Asterisk\Cdr;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

class ExternalController extends BaseController
{

    public function __construct ( Request $request )
    {
        $this->setLogs( storage_path( 'logs/rest_external.log' ) );
        parent::__construct( $request );
    }

    public function works ( Request $request ) : Response
    {

        if ( ! $this->checkProviderKey( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }

        $validation = \Validator::make( $request->all(), [
            'segment_id'         => 'nullable|integer',
        ]);

        if ( $validation->fails() )
        {
            return $this->error( $validation->errors()->first() );
        }
		
		if ( \Cache::tags( 'external' )->has( 'external.works.' . $this->providerKey->provider_id . '.' . $request->get( 'segment_id', '0' ) ) )
        {
            $response = \Cache::tags( 'external' )->get( 'external.works.' . $this->providerKey->provider_id . '.' . $request->get( 'segment_id', '0' ) );
        }
        else
        {

			$works = Work
				::where( 'provider_id', '=', $this->providerKey->provider_id )
				->current();

			if ( $request->get( 'segment_id' ) )
			{
				$segment = Segment::find( $request->get( 'segment_id' ) );
				if ( ! $segment )
				{
					return $this->error( 'Сегмент не найден' );
				}
				$segmentChilds = new SegmentChilds( $segment );
				$ids = $segmentChilds->ids;
				$works
					->whereHas( 'buildings', function ( $buildings ) use ( $ids )
					{
						return $buildings
							->where( 'lon', '!=', - 1 )
							->where( 'lat', '!=', - 1 )
							->whereIn( 'segment_id', $ids );
					});
			}
			else
			{
				$works
					->whereHas( 'buildings', function ( $buildings )
					{
						return $buildings
							->where( 'lon', '!=', - 1 )
							->where( 'lat', '!=', - 1 );
					});
			}

			$works = $works
				->with( 'buildings' )
				->get();

			$data = [];
			foreach ( $works as $work )
			{
				foreach ( $work->buildings as $building )
				{
					if ( ! isset( $data[ $building->id ] ) )
					{
						if ( $building->lon && $building->lat )
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
					}
					$managements = $work->managements()->mine()->get()->implode( 'name', '; ' );
					$data[ $building->id ][ 'works' ][] = [
						'id'                => $work->id,
						//'url'               => route( 'works.show', $r->id ),
						'management'        => $managements,
						'composition'       => $work->composition,
						'category'          => $work->category->name,
						'category_id'       => $work->category->id,
						'time_end'          => $work->time_end->format( 'd.m.Y H:i' ),
					];
				}
			}
			
			$response = array_values( $data );
			
			\Cache::tags( 'external' )->put( 'external.works.' . $this->providerKey->provider_id . '.' . $request->get( 'segment_id', '0' ), $response, 5 );
			
		}

        return $this->success( $response );

    }
	
	public function statistics ( Request $request )
	{
		
		if ( ! $this->checkProviderKey( $request, $error, $httpCode ) )
        {
            return $this->error( $error, $httpCode );
        }
		
		if ( \Cache::tags( 'external' )->has( 'external.statistics.' . $this->providerKey->provider_id ) )
        {
            $response = \Cache::tags( 'external' )->get( 'external.statistics.' . $this->providerKey->provider_id );
        }
        else
        {
		
			$dt = Carbon::now()->subDays( 7 )->setTime( 0, 0, 0 );
			
			$calls = Cdr
				::where( function ( $q )
				{
					return $q
						->whereIn( 'dcontext', $this->providerKey->provider->contexts->pluck( 'context' ) )
						->orWhereIn( \DB::raw( 'RIGHT( dst, 10 )' ), $this->providerKey->provider->phones->pluck( 'context' ) );
				})
				->where( 'calldate', '>=', $dt->toDateTimeString() )
				->where( 'dst', '!=', 's' )
				->where( 'dcontext', '=', 'incoming' )
				->groupBy(
					'uniqueid',
					'disposition'
				)
				->get();
								
			$tickets = Ticket
				::where( 'provider_id', '=', $this->providerKey->provider_id )
				->where( 'status_code', '!=', 'draft' )
				->where( 'created_at', '>=', $dt->toDateTimeString() )
				->count();
				
			$ticketsClosed = Ticket
				::where( 'provider_id', '=', $this->providerKey->provider_id )
				->whereIn( 'status_code', [ 'closed_with_confirm', 'closed_without_confirm' ] )
				->where( 'created_at', '>=', $dt->toDateTimeString() )
				->count();
				
			$ticketsRates = TicketManagement
				::whereHas( 'ticket', function ( $ticket ) use ( $dt )
				{
					return $ticket
						->where( 'provider_id', '=', $this->providerKey->provider_id )
						->where( 'created_at', '>=', $dt->toDateTimeString() );
				})
				->whereNotNull( 'rate' )
				->get()
				->avg( 'rate' );
								
			$response = [
				'calls'					=> (int) $calls->count(),
				'tickets'				=> (int) $tickets,
				'tickets_closed'		=> (int) $ticketsClosed,
				'rate_avg'				=> (float) number_format( $ticketsRates, 1 ),
			];
			
			\Cache::tags( 'external' )->put( 'external.statistics.' . $this->providerKey->provider_id, $response, 15 );
			
		}
		
		return $this->success( $response );
		
	}

}