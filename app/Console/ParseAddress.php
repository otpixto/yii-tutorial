<?php

namespace App\Console;

use App\Models\Operator\Address;
use App\Models\Operator\Management;
use Illuminate\Console\Command;

class ParseAddress extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:addresses';


    public function __construct ()
    {
        parent::__construct ();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle ()
    {

        $addresses = \App\Models\Address::all();
        foreach ( $addresses as $address )
        {
            $exp = explode( ',', $address->name );
            print_r( $exp );
            if ( count( $exp ) < 2 ) continue;
            $house = trim( str_replace( 'д.', ',', array_pop( $exp ) ) );
            $address = implode( ',', $exp );
            echo $address . ' ' . $house . PHP_EOL;
        }

    }

}