<?php

namespace App\Console\Commands;

use App\Models\Building;
use Illuminate\Console\Command;


class FixAddresses extends Command
{

    protected $signature = 'fix:addresses';

    public function __construct ()
    {
        parent::__construct();
    }

    public function handle ()
    {

        $buildings = Building
            ::whereNull( 'number' )
            ->orWhereNull( 'segment_id' )
            ->get();

        $types = [];

        foreach ( $buildings as $building )
        {
            $exp = explode( ',', $building->name );
            $cnt = count( $exp );
            if ( mb_strpos( $exp[ $cnt - 1 ], 'д.' ) !== false )
            {
                if ( ! $building->number )
                {
                    $building->number = trim( mb_substr( $exp[ $cnt - 1 ], 3 ) );
                    $building->save();
                }
                array_pop( $exp );
                //echo $building->number . PHP_EOL;
            }
            if ( ! isset( $exp[ 0 ] ) ) continue;
            $first = trim( $exp[ 0 ] );
            $types[ $first ] = 1;
            /*foreach ( $exp as $item )
            {
                $item = trim( $item );
                if ( mb_strpos( $item, 'гараж' ) !== false ) continue;
                if ( mb_strpos( $item, 'стр' ) !== false ) continue;
                if ( mb_strpos( $item, 'здание' ) !== false ) continue;
                if ( mb_strpos( $item, 'км' ) !== false ) continue;
                $types[ $item ] = 1;
            }*/
        }

        print_r( $types );

    }

}