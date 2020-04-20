<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillSpAddressesForSp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $buildings = \App\Models\Building::where('name', 'like', "%Сергиево-Посадский р-н%")->get();
        if (count($buildings)) {
            foreach ($buildings as $building) {

                    $segmentId = null;
                    if($building->segment) {
                        $segment = \App\Models\Segment::where('provider_id', 12)->where('name', 'like', $building->segment->name)->first();

                        if (!$segment) {
                            $segment = new \App\Models\Segment();
                            $segment->parent_id = 2977;
                            $segment->provider_id = 12;
                            $segment->segment_type_id = 3;
                            $segment->name = $building->segment->name;
                            $segment->save();
                        }
                        $segmentId = $segment->id;
                    }

                    $building->provider_id = 12;
                    $building->segment_id = $segmentId;

                    $building->save();

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
