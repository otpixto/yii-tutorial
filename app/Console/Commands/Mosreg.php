<?php

namespace App\Console\Commands;

use App\Classes\MosregClient;
use App\Models\Building;
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
                ::whereNotNull( 'mosreg_username' )
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
                return ! in_array( $item->id, $this->buildings );
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
                    $address_exp = explode( ', ', $building->getAddress() );
                    $address = '';
                    foreach ( $address_exp as $e )
                    {
                        if ( mb_strpos( $e, 'ул.' ) !== false )
                        {
                            $address .= ' ' . $e;
                        }
                        else if ( mb_strpos( $e, 'д.' ) !== false )
                    {
                        $address .= ' ' . $e;
                    }
                    }
                    $address = trim( $address );
                    if ( mb_strlen( $address ) < 5 )
                    {
                        continue;
                    }
                    $this->line( "\t" . $address . ' (' . $building->id . ')' );
                    $res = $mosreg->searchAddress( $address, true );
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
                        $this->buildings[] = $building->id;
                    }
                    else
                    {
                        $this->selectAddress( $building, $res );
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

    private function selectAddress ( Building $building, $res = [] )
    {
        $values = [];
        usort( $res, function ( $a, $b )
        {
            return mb_strlen( $a->label ) - mb_strlen( $b->label );
        });
        foreach ( $res as $i => $r )
        {
            $values[] = [
                $i,
                $r->label,
            ];
        }
        $this->table( [ '#', 'Address' ], $values );
        $answer = $this->ask("\t\t" .'Выберите адрес', 0 );
        if ( isset( $res[ $answer ] ) )
        {
            $building->mosreg_id = $res[ $answer ]->addressId;
            $building->save();
            $this->buildings[] = $building->id;
        }
        else
        {
            $this->error( "\t\t" .'Некорректный выбор' );
            return $this->selectAddress( $building, $res );
        }
    }

}