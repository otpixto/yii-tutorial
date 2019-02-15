<?php

namespace App\Http\Controllers\Rest;

use App\Classes\SegmentChilds;
use App\Models\Segment;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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

        return $this->success( array_values( $data ) );

    }

}