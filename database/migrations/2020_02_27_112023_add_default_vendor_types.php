<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultVendorTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $parentTypes = \App\Models\Type::join( 'types_vendors', 'types_vendors.type_id', '=', 'types.id' )->where('vendor_id', 2)->get();

        foreach ($parentTypes as $parentType)
        {

            \Illuminate\Support\Facades\DB::table( 'types_vendors' )
                ->insert(
                    [ 'type_id' => $parentType->type_id, 'vendor_id' => 7 ]
                );

            $children = \App\Models\Type::where('parent_id', $parentType->type_id)->get();
            foreach ($children as $child)
            {
                \Illuminate\Support\Facades\DB::table( 'types_vendors' )
                    ->insert(
                        [ 'type_id' => $child->id, 'vendor_id' => 7 ]
                    );
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
