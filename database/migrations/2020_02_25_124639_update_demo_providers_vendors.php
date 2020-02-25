<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateDemoProvidersVendors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $vendors = [ 3,  5, 7 ];

        $demoTypes = \App\Models\Type::where('provider_id', 5)->get();

        foreach($demoTypes as $type){
            if ( count( $vendors ) )
            {
                $type->vendors()
                    ->detach();

                if(is_array($vendors)) {
                    foreach ( $vendors as $vendorID )
                    {
                        \Illuminate\Support\Facades\DB::table( 'types_vendors' )
                            ->insert(
                                [ 'type_id' => $type->id, 'vendor_id' => (int) $vendorID ]
                            );
                    }
                }

            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
