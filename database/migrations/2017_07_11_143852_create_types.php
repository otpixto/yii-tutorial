<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try
        {
            Schema::create( 'types', function ( Blueprint $table )
            {
                $table->increments('id' );
                $table->string('name' )->unique();
                $table->integer('category_id' )->unsigned();
                $table->float('period_acceptance' )->default( 0 );
                $table->float('period_execution' )->default( 0 );
                $table->boolean('need_act' )->default( 0 );
                $table->string( 'season' )->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
        catch ( PDOException $e )
        {
            $this->down();
            throw $e;
        }
        catch ( \Illuminate\Database\QueryException $e )
        {
            $this->down();
            throw $e;
        }
        catch ( Exception $e )
        {
            $this->down();
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('types');
    }
}
