<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusesHistory extends Migration
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
            Schema::create( 'statuses_history', function ( Blueprint $table )
            {
                $table->increments('id' );
                $table->integer('author_id' )->unsigned();
                $table->integer('model_id' )->unsigned();
				$table->string('model_name' );
                $table->string('status_code' );
                $table->string('status_name' );
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
        Schema::dropIfExists( 'statuses_history' );
    }
}
