<?php

namespace App\Models;

class Geometry extends BaseModel
{

    protected $table = 'geometry';
    public static $_table = 'geometry';

    public static $name = 'Объекты на карте';

    protected $nullable = [
        'provider_id',
        'type',
        'fillColor',
        'strokeColor',
        'preset',
    ];

    protected $fillable = [
        'name',
        'management_id',
        'type',
        'coordinates',
        'fillColor',
        'strokeColor',
        'preset',
    ];

    public static $rules = [
        'name'			            => 'required|string|max:255',
        'management_id'			    => 'required|integer',
        'provider_id'			    => 'nullable|integer',
        'type'		                => 'required_without:id|in:Point,Polygon',
        'coordinates'			    => 'required|json',
        'fillColor'			        => 'required_without:preset|string|max:7',
        'strokeColor'			    => 'required_without:preset|string|max:7',
        'preset'			        => 'required_without:fillColor,strokeColor|string|max:50',
    ];

    public static $presets = [
        'islands#blueDotIcon',
        'islands#darkGreenDotIcon',
        'islands#redDotIcon',
        'islands#violetDotIcon',
        'islands#darkOrangeDotIcon',
        'islands#blackDotIcon',
        'islands#nightDotIcon',
        'islands#yellowDotIcon',
        'islands#darkBlueDotIcon',
        'islands#greenDotIcon',
        'islands#pinkDotIcon',
        'islands#orangeDotIcon',
        'islands#grayDotIcon',
        'islands#lightBlueDotIcon',
        'islands#brownDotIcon',
        'islands#oliveDotIcon',
        /*'islands#blueAirportIcon',
        'islands#blueAttentionIcon',
        'islands#blueAutoIcon',
        'islands#blueBarIcon',
        'islands#blueBarberIcon',
        'islands#blueBeachIcon',
        'islands#blueBicycleIcon',
        'islands#blueBicycle2Icon',
        'islands#blueBookIcon',
        'islands#blueCarWashIcon',
        'islands#blueChristianIcon',
        'islands#blueCinemaIcon',
        'islands#blueCircusIcon',
        'islands#blueCourtIcon',
        'islands#blueDeliveryIcon',
        'islands#blueDiscountIcon',
        'islands#blueDogIcon',
        'islands#blueEducationIcon',
        'islands#blueEntertainmentCenterIcon',
        'islands#blueFactoryIcon',
        'islands#blueFamilyIcon',
        'islands#blueFashionIcon',
        'islands#blueFoodIcon',
        'islands#blueFuelStationIcon',
        'islands#blueGardenIcon',
        'islands#blueGovernmentIcon',
        'islands#blueHeartIcon',
        'islands#blueHomeIcon',
        'islands#blueHotelIcon',
        'islands#blueHydroIcon',
        'islands#blueInfoIcon',
        'islands#blueLaundryIcon',
        'islands#blueLeisureIcon',
        'islands#blueMassTransitIcon',
        'islands#blueMedicalIcon',
        'islands#blueMoneyIcon',
        'islands#blueMountainIcon',
        'islands#blueNightClubIcon',
        'islands#blueObservationIcon',
        'islands#blueParkIcon',
        'islands#blueParkingIcon',
        'islands#bluePersonIcon',
        'islands#bluePocketIcon',
        'islands#bluePoolIcon',
        'islands#bluePostIcon',
        'islands#blueRailwayIcon',
        'islands#blueRapidTransitIcon',
        'islands#blueRepairShopIcon',
        'islands#blueRunIcon',
        'islands#blueScienceIcon',
        'islands#blueShoppingIcon',
        'islands#blueSouvenirsIcon',
        'islands#blueSportIcon',
        'islands#blueStarIcon',
        'islands#blueTheaterIcon',
        'islands#blueToiletIcon',
        'islands#blueUnderpassIcon',
        'islands#blueVegetationIcon',
        'islands#blueVideoIcon',
        'islands#blueWasteIcon',
        'islands#blueWaterParkIcon',
        'islands#blueWaterwayIcon',
        'islands#blueWorshipIcon',
        'islands#blueZooIcon'*/
    ];

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

    public function scopeMine ( $query )
    {
        return $query
            ->whereNull( self::$_table . '.provider_id' )
            ->orWhereHas( 'provider', function ( $provider )
            {
                return $provider
                    ->mine()
                    ->current();
            })
            ->orWhereHas( 'management', function ( $management )
            {
                return $management
                    ->mineProvider();
            });
    }

}
