<?php

namespace App\Console\Commands;

use App\Models\Building;
use App\Models\Management;
use App\Models\Provider;
use App\Models\Type;
use Illuminate\Console\Command;

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

        $addresses = Building::whereDoesntHave( 'providers' )->get();
        foreach ( $addresses as $address )
        {
            if ( ! $address->providers->contains( 'id', $address->provider_id ) )
            {
                $address->providers()->attach( $address->provider_id );
            }
        }

        $addresses = Building::whereIn( 'provider_id', [ 1, 3 ] )->get();
        foreach ( $addresses as $address )
        {
            if ( ! $address->providers->contains( 'id', 6 ) )
            {
                $address->providers()->attach( 6 );
            }
        }

        $managements = Management::whereDoesntHave( 'providers' )->get();
        foreach ( $managements as $management )
        {
            if ( ! $management->providers->contains( 'id', $management->provider_id ) )
            {
                $management->providers()->attach( $management->provider_id );
            }
        }

        $types = Type::whereDoesntHave( 'providers' )->get();
        foreach ( $types as $type )
        {
            $type->providers()->sync( Provider::pluck( 'id' ) );
        }

        \DB::commit();
        //\DB::rollBack();

    }

}