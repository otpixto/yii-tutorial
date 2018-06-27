<?php

namespace App\Console\Commands;

use App\Models\Address;
use App\Models\Management;
use Illuminate\Console\Command;
use Illuminate\Support\MessageBag;

class FixRelations extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:relations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Правки связей';

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

        $this->info( 'Обрабатываю адреса...' );

        $addresses = Address::whereDoesntHave( 'regions' )->get();
        foreach ( $addresses as $address )
        {
            $address->regions()->attach( $address->region_id );
        }

        $addresses = Address::whereIn( 'region_id', [ 1, 3 ] )->get();
        foreach ( $addresses as $address )
        {
            $address->regions()->attach( 6 );
        }

        $managements = Management::whereDoesntHave( 'regions' )->get();
        foreach ( $managements as $management )
        {
            $management->regions()->attach( $management->region_id );
        }

        \DB::commit();
        //\DB::rollBack();

    }

}