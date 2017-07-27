<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkflow extends Migration
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
            Schema::create( 'workflow', function ( Blueprint $table )
            {
                $table->increments('id' );
                $table->integer('model_id' )->unsigned();
                $table->string('model_name' );
                $table->integer('bind_model_id' )->unsigned();
                $table->string('bind_model_name' );
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
        Schema::dropIfExists( 'workflow' );
    }
}
