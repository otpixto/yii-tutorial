<?php

namespace App\Console\Commands;

use App\Models\Address;
use Illuminate\Console\Command;
use Illuminate\Support\MessageBag;

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

        \DB::beginTransaction();

        $addresses = Address::all();
        foreach ( $addresses as $address )
        {
            $hash = Address::genHash( $address->name );
            $addr = Address::where( 'hash', '=', $hash )->count();
            if ( ! $addr )
            {
                $address->hash = $hash;
                $address->save();
            }
        }

        if ( ( $handle = fopen( storage_path( 'files/juk_addr.csv' ), 'r' ) ) !== FALSE )
        {
            while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== FALSE )
            {
                $address_name = trim( $data[0] );
                if ( ! empty( $data[1] ) )
                {
                    $address_name .= ', ' . trim( $data[1] );
                }
                $address = Address
                    ::search( $address_name )
                    ->first();
                if ( ! $address )
                {
                    $address = Address::create([
                        'name' => $address_name,
                        'region_id' => 6
                    ]);
                    if ( $address instanceof MessageBag )
                    {
                        dd( $address );
                    }
                    $address->save();
                }
                $address->regions()->attach( 6 );
            }
            fclose( $handle );
        }

        if ( ( $handle = fopen( storage_path( 'files/ram_addr.csv' ), 'r' ) ) !== FALSE )
        {
            while ( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== FALSE )
            {
                $address_name = trim( $data[0] );
                if ( ! empty( $data[1] ) )
                {
                    $address_name .= ', ' . trim( $data[1] );
                }
                $address = Address
                    ::search( $address_name )
                    ->first();
                if ( ! $address )
                {
                    $address = Address::create([
                        'name' => $address_name,
                        'region_id' => 6
                    ]);
                    if ( $address instanceof MessageBag )
                    {
                        dd( $address );
                    }
                    $address->save();
                }
                $address->regions()->attach( 6 );
            }
            fclose( $handle );
        }

        \DB::commit();
        //\DB::rollBack();

    }

}