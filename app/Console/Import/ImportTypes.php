<?php

namespace App\Console\Commands;

use App\Models\Provider;
use App\Models\Type;
use Illuminate\Console\Command;

class ImportTypes extends Command
{

    protected $signature = 'import:types';

    protected $description = 'Импорт классификатора с одного поставщика на другого';

    public function __construct ()
    {
        parent::__construct ();
    }

    public function handle ()
    {

        try
        {

            $this->line( $this->description );

            $providers = Provider::orderBy( 'name' )->pluck( 'name', 'id' )->toArray();

            $provider_from_name = $this->choice('С какого поставщика перенести?', $providers );
            $provider_from_id = array_search( $provider_from_name, $providers );

            $provider_to_name = $this->choice('На какого поставщика перенести?', $providers );
            $provider_to_id = array_search( $provider_to_name, $providers );

            if ( $provider_from_id == $provider_to_id )
            {
                return $this->error( 'Выберите другого поставщика' );
            }

            $types = Type
                ::where( 'provider_id', '=', $provider_from_id )
                ->whereNull( 'parent_id' )
                ->get();

            $typesCount = $types->count();

            $bar = $this->output->createProgressBar( $typesCount );

            \DB::beginTransaction();

            foreach ( $types as $type )
            {
                $attributes = $type->toArray();
                unset( $attributes[ 'id' ] );
                $attributes[ 'group_id' ] = null;
                $attributes[ 'category_id' ] = null;
                $attributes[ 'provider_id' ] = $provider_to_id;
                $parentType = Type::create( $attributes );
                $parentType->save();
                foreach ( $type->childs as $child )
                {
                    $attributes = $child->toArray();
                    unset( $attributes[ 'id' ] );
                    $attributes[ 'group_id' ] = null;
                    $attributes[ 'category_id' ] = null;
                    $attributes[ 'parent_id' ] = $parentType->id;
                    $attributes[ 'provider_id' ] = $provider_to_id;
                    $childType = Type::create( $attributes );
                    $childType->save();
                }
                $bar->advance();
            }

            \DB::commit();

            $bar->finish();

            $this->line( 'Импорт успешно завершен' );

        }
        catch ( \Exception $e )
        {
            $this->error( $e->getMessage() );
        }

    }

}