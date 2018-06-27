<?php

namespace App\Console\Commands;

use App\Models\Address;
use App\Models\Management;
use App\Models\Region;
use App\Models\Type;
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

        $addresses = Address::whereDoesntHave( 'regions' )->get();
        foreach ( $addresses as $address )
        {
            if ( ! $address->regions->contains( 'id', $address->region_id ) )
            {
                $address->regions()->attach( $address->region_id );
            }
        }

        $addresses = Address::whereIn( 'region_id', [ 1, 3 ] )->get();
        foreach ( $addresses as $address )
        {
            if ( ! $address->regions->contains( 'id', 6 ) )
            {
                $address->regions()->attach( 6 );
            }
        }

        $managements = Management::whereDoesntHave( 'regions' )->get();
        foreach ( $managements as $management )
        {
            if ( ! $management->regions->contains( 'id', $management->region_id ) )
            {
                $management->regions()->attach( $management->region_id );
            }
        }

        $types = Type::whereDoesntHave( 'regions' )->get();
        foreach ( $types as $type )
        {
            $type->regions()->sync( Region::pluck( 'id' ) );
        }

        \DB::commit();
        //\DB::rollBack();

    }

}