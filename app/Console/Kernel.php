<?php

namespace App\Console;

use App\Classes\GzhiHandler;
use App\Console\Commands\CreateSegments;
use App\Console\Commands\CreateUser;
use App\Console\Commands\FixAddresses;
use App\Console\Commands\FixDates;
use App\Console\Commands\FixRelations;
use App\Console\Commands\Grub;
use App\Console\Commands\ImportAddress;
use App\Console\Commands\ImportTypes;
use App\Console\Commands\ImportVerin;
use App\Console\Commands\Mosreg;
use App\Console\Commands\Sync;
use App\Console\Commands\TestTelnet;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Sync::class,
        FixDates::class,
        FixAddresses::class,
        ImportAddress::class,
        FixRelations::class,
        Grub::class,
        ImportVerin::class,
        CreateUser::class,
        Mosreg::class,
        CreateSegments::class,
        ImportTypes::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule ( Schedule $schedule )
    {

        $schedule->call(function (){
            (new GzhiHandler())->sendGzhiInfo();
        })
            ->dailyAt('17:15');

        $schedule->call( function ()
        {
            ( new GzhiHandler() )->getGzhiRequestsStatus();
        } )
            ->dailyAt('17:25');

    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands ()
    {
        //require base_path('routes/console.php');
    }
}
