<?php

namespace App\Console;

use App\Console\Commands\FixDates;
use App\Console\Commands\FixRelations;
use App\Console\Commands\Grub;
use App\Console\Commands\ImportAddress;
use App\Console\Commands\Sync;
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
        ImportAddress::class,
        FixRelations::class,
        Grub::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule ( Schedule $schedule )
    {
         //
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
