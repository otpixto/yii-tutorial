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

        $addresses = \App\Models\Address::whereNull( 'address' )->get();
        foreach ( $addresses as $addr )
        {
            $exp = explode( 'д.', $addr->name );
            if ( count( $exp ) != 2 ) continue;
            $home = trim( str_replace( ',', '', $exp[1] ) );
            $address = trim( trim( $exp[0] ), ',' );
            $addr->address = $address;
            $addr->home = $home;
            $addr->save();
        }

    }

}