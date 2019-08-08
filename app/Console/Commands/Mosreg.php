<?php

namespace App\Console\Commands;

use App\Classes\MosregClient;
use App\Models\Management;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class Mosreg extends Command
{

    protected $signature = 'sync:mosreg';

    protected $description = 'Mosreg';

    private $buildings = [];

    public function __construct ()
    {
        parent::__construct();
    }

    public function handle ()
    {
        try
        {
            $managements = Management
                ::whereNotNull( 'mosreg_id' )
                ->whereNotNull( 'mosreg_username' )
                ->whereNotNull( 'mosreg_password' )
                ->where( function ( $q )
                {
                    return $q
                        ->whereHas( 'buildings', function ( $buildings )
                        {
                            return $buildings
                                ->whereNull( 'mosreg_id' );
                        })
                        ->orWhereHas( 'childs', function ( $childs )
                        {
                            return $childs
                                ->whereHas( 'buildings', function ( $buildings )
                                {
                                    return $buildings
                                        ->whereNull( 'mosreg_id' );
                                });
                        });
                })
                ->get();
            foreach ( $managements as $management )
            {
                try
                {
                    $this->line( $management->name . ' (' . $management->id . ')' );
                    $mosreg = null;
                    if ( $management->hasMosreg( $mosreg ) )
                    {
                        $buildings = $this->getBuildings( $management );
                        $this->parseBuildings( $mosreg, $buildings );
                        foreach ( $management->childs as $child )
                        {
                            $buildings = $this->getBuildings( $child );
                            if ( $buildings->count() )
                            {
                                $this->line( $child->name . ' (' . $child->id . ')' );
                                $this->parseBuildings( $mosreg, $buildings );
                            }
                        }
                    }
                }
                catch ( \Exception $e )
                {
                    $this->error( $e->getMessage() );
                }
            }
        }
        catch ( \Exception $e )
        {
            $this->error( $e->getMessage() );
        }
    }

    private function getBuildings ( Management $management )
    {
        $buildings = $management
            ->buildings()
            ->whereNull( 'mosreg_id' )
            ->get()
            ->filter( function ( $item )
            {
                $return = ! in_array( $item->id, $this->buildings );
                $this->buildings[] = $item->id;
                return $return;
            });
        return $buildings;
    }

    private function parseBuildings ( MosregClient $mosreg, Collection $buildings )
    {
        try
        {
            if ( ! $buildings->count() ) return;
            foreach ( $buildings as $building )
            {
                try
                {
                    $this->line( "\t" . $building->name . ' (' . $building->id . ')' );
                    $res = $mosreg->searchAddress( $building->name, true );
                    $cnt = count( $res );
                    if ( ! $cnt )
                    {
                        $this->warn( "\t\t" . 'Ничего не найдено' );
                    }
                    else if ( count( $res ) == 1 )
                    {
                        $this->info( "\t\t" .'Адрес найден' );
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
                        $this->table( array_keys( $values ), $values );
                        $answer = $this->anticipate("\t\t" .'Выберите адрес', $values, 0 );
                        if ( isset( $res[ $answer ] ) )
                        {
                            $building->mosreg_id = $res[ $answer ]->addressId;
                            $building->save();
                        }
                        else
                        {
                            $this->error( "\t\t" .'Некорректный выбор' );
                        }
                    }
                }
                catch ( \Exception $e )
                {
                    $this->error( $e->getMessage() );
                }
            }
        }
        catch ( \Exception $e )
        {
            $this->error( $e->getMessage() );
        }
    }

}