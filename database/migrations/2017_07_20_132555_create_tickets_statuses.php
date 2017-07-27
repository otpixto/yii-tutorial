<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsStatuses extends Migration
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
            if ( !Schema::hasTable( 'tickets_statuses' ) )
            {
                Schema::create( 'tickets_statuses', function ( Blueprint $table )
                {
                    $table->increments('id' );
                    $table->integer('ticket_id' )->unsigned();
                    $table->enum( 'status', [ 'draft','accepted_operator','perform','accepted_management','done','done_without_act','closed_success','closed_without_confirm','not_confirmed','not_done','cancel','failure' ] );
                    $table->decimal( 'hours', 5, 2 )->default( 0 );
                    $table->timestamps();
                    $table->softDeletes();
                });
            }
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
        Schema::dropIfExists( 'tickets_statuses' );
    }
}
