<?php

namespace App\Console\Commands;

use App\Models\Building;
use Illuminate\Console\Command;


class FixAddresses extends Command
{

    protected $signature = 'fix:addresses';

    public function handle ()
    {

        $buildings = Building
            ::whereNull( 'number' )
            ->orWhereNull( 'segment_id' )
            ->get();

        foreach ( $buildings as $building )
        {
            $save = false;
            $name = trim( $building->name );
            if ( mb_substr( $name, -1 ) == ',' )
            {
                $name = mb_substr( $name, 0, mb_strlen( $name ) - 1 );
                $building->name = $name;
                $save = true;
            }
            $exp = explode( ',', $name );
            $cnt = count( $exp );
            $number = trim( $exp[ $cnt - 1 ] );
            if ( mb_strpos( $number, 'д.' ) !== false )
            {
                if ( ! $building->number )
                {
                    $building->number = trim( mb_substr( $number, 2 ) );
                    $save = true;
                }
                //echo $building->number . PHP_EOL;
            }
            if ( $save )
            {
                $building->save();
            }
        }

    }

}