<?php

namespace App\Console\Commands;

use App\Classes\Gzhi;
use App\Models\Address;
use App\Models\Region;
use Illuminate\Console\Command;
use Illuminate\Support\MessageBag;

class Sync extends Command
{

    protected $signature = 'gzhi:sync {select}';

    public function fire ()
    {

        $this->info( 'Добро пожаловать в программу для синхронизации с АИС ГЖИ!' );
        $this->info( 'Версия АИС ГЖИ ' . \Config::get( 'gzhi.version' ) );

        $regions = Region
            ::orderBy( 'name' )
            ->whereNotNull( 'guid' )
            ->whereNotNull( 'username' )
            ->whereNotNull( 'password' )
            ->get();
        $region = null;
        $choice_all = ' -- ВСЕ -- ';

        $choice = $this->choice('Выберите регион для синхронизации', array_merge( [ $choice_all ], $regions->pluck( 'name' )->toArray() ), 0 );

        if ( $choice != $choice_all )
        {
            $region = $regions->where( 'name', $choice )->first();
            $this->info( 'Выбрана синхронизация для региона "' . $region->name . '"' );
        }
        else
        {
            $this->info( 'Выбрана синхрониазация для всех регионов' );
        }

        switch ( $this->argument( 'select' ) )
        {

            case 'addresses':

                if ( ! $region )
                {
                    foreach ( $regions as $region )
                    {
                        $this->info( 'Началась синхрониазация адресов для региона "' . $region->name . '"' );
                        $this->syncAddresses( $region );
                    }
                }
                else
                {
                    $this->info( 'Началась синхрониазация адресов для региона "' . $region->name . '"' );
                    $this->syncAddresses( $region );
                }

            break;
        }

        $this->info( PHP_EOL . 'Синхронизация окончена' );

    }

    public function syncAddresses ( Region $region )
    {

        $client = new Gzhi( $region->getGzhiConfig() );

        $rules = [
            'guid'                  => 'nullable|unique:addresses,guid|regex:/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i',
            'region_id'             => 'required|integer',
            'name'                  => 'required|string|max:255|unique:addresses,name',
        ];

        try
        {

            $this->info( 'Загружаю адреса для региона "' . $region->name . '"' );
            $response = $client->GetAddresses();
            //$response = $client->GetResult( 'b0019410-e4cd-11e7-82b7-05d37e8c944e' );
            $addresses = $response->Addresses;
            $count = count( $addresses );
            $this->info( 'Готово. Загружено адресов: ' . $count );
            $bar = $this->output->createProgressBar( $count );
            foreach ( $addresses as $address )
            {
                $bar->advance();
                $attributes = [
                    'guid'          => $address->AddressGUID,
                    'region_id'     => $region->id,
                    'name'          => $address->AddressName
                ];
                $v = \Validator::make( $attributes, $rules );
                if ( $v->fails() )
                {
                    /*foreach ( $v->errors()->all() as $error )
                    {
                        $this->warn( $error );
                    }*/
                    continue;
                }
                $res = Address::create( $attributes );
                if ( $res instanceof MessageBag )
                {
                    /*foreach ( $res->errors()->all() as $error )
                    {
                        $this->error( $error );
                    }*/
                    continue;
                }
                $res->save();
            }
            $bar->finish();
        }
        catch ( \SoapFault $e )
        {
            $this->error( 'SoapFault: ' . $e->faultstring . ' (' . $e->faultcode . ')' );
        }

    }

}