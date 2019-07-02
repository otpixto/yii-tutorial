<?php

namespace App\Console\Commands;

use App\Models\Management;
use Illuminate\Console\Command;

class Mosreg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:mosreg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mosreg';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct ()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle ()
    {

        $managements = Management
            ::whereNotNull( 'mosreg_username' )
            ->whereNotNull( 'mosreg_password' )
            ->whereNotNull( 'mosreg_id' )
            ->get();
        foreach ( $managements as $management )
        {

            $buildings = $management
                ->buildings()
                ->whereNull( 'mosreg_id' )
                ->get();
            if ( ! $buildings->count() ) continue;
            //$bar = $this->output->createProgressBar( $buildings->count() );
            $mosreg = new \App\Classes\MosregClient( $management->mosreg_id, $management->mosreg_username, $management->mosreg_password );
            foreach ( $buildings as $building )
            {
                //$bar->advance();
                $this->line( $building->name );
                $res = $mosreg->searchAddress( $building->name, true );
                $cnt = count( $res );
                if ( ! $cnt )
                {
                    $this->warn( 'Ничего не найдено' );
                }
                else if ( count( $res ) == 1 )
                {
                    $this->info( 'Адрес найден' );
                    $building->mosreg_id = $res[ 0 ]->addressId;
                    $building->save();
                }
                else
                {
                    $values = [];
                    foreach ( $res as $r )
                    {
                        $values[] = $r->label;
                    }
                    print_r( $values );
                    $answer = $this->anticipate('Выберите адрес', $values, 0 );
                    if ( isset( $res[ $answer ] ) )
                    {
                        $building->mosreg_id = $res[ $answer ]->addressId;
                        $building->save();
                    }
                    else
                    {
                        $this->error( 'Некорректный выбор' );
                    }
                }
            }
        }
        //$bar->finish();
    }
}