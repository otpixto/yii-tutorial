<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FillJukAddressesForHotLine extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        $buildings = \App\Models\Building::where('name', 'like', "%г. Жуковский%")->get();
        if (count($buildings)) {
            foreach ($buildings as $building) {
                $newBuilding = \App\Models\Building::where('provider_id', 10)->where('name', 'like', $building->name)->first();
                if (!$newBuilding) {
                    $segment = \App\Models\Segment::where('provider_id', 10)->where('name', 'like', $building->segment->name)->first();

                    if (!$segment) {
                        $segment = new \App\Models\Segment();
                        $segment->parent_id = 2742; //2742
                        $segment->provider_id = 10;
                        $segment->segment_type_id = 3;
                        $segment->name = $building->segment->name;
                        $segment->save();
                    }

                    $newBuilding = new \App\Models\Building();
                    $newBuilding->provider_id = 10;
                    $newBuilding->segment_id = $segment->id;
                    $newBuilding->building_type_id = $building->building_type_id;
                    $newBuilding->name = $building->name;
                    $newBuilding->number = $building->number;
                    $newBuilding->hash = $building->hash;
                    $newBuilding->guid = $building->guid;
                    $newBuilding->lon = $building->lon;
                    $newBuilding->lat = $building->lat;
                    $newBuilding->mosreg_id = $building->mosreg_id;
                    $newBuilding->gzhi_address_guid = $building->gzhi_address_guid;
                    $newBuilding->fais_address_guid = $building->fais_address_guid;
                    $newBuilding->save();
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
