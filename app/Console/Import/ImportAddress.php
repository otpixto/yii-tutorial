<?php

namespace App\Console\Commands;

use App\Models\Operator\Address;
use App\Models\Operator\Management;
use Illuminate\Console\Command;

class ImportAddress extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:addresses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт адресов и привязанных к ним УК';

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

        if ( ( $handle = fopen( storage_path( 'files/addresses.csv' ), 'r' ) ) !== FALSE )
        {
            while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== FALSE )
            {
                $management_name = $data[1];
                $address_name = $data[0];
                $management = Management
                    ::where( 'name', '=', $management_name )
                    ->first();
                if ( !$management )
                {
                    $management = Management::create([
                        'name' => $management_name
                    ]);
                }
                $address = Address
                    ::where( 'name', '=', $address_name )
                    ->first();
                if ( !$address )
                {
                    $address = Address::create([
                        'name' => $address_name,
                        'management_id' => $management->id
                    ]);
                }
            }
            fclose( $handle );
        }

    }

}