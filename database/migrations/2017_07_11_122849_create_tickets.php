<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTickets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up ()
    {
        try
        {
            Schema::create( 'tickets', function ( Blueprint $table )
            {
                $table->increments('id' );
                $table->integer('author_id' )->unsigned();
                $table->integer('type_id' )->unsigned();
                $table->integer('management_id' )->unsigned();
                $table->integer('address_id' )->unsigned();
                $table->integer('customer_id' )->unsigned();
                $table->string('firstname' );
                $table->string('middlename' )->nullable();
                $table->string('lastname' )->nullable();
                $table->string('phone' );
                $table->string('phone2' )->nullable();
                $table->string('text' );
                $table->enum( 'status', [ 'new', 'accepted', 'execution', 'done', 'closed_success', 'closed_fail', 'cancel' ] )->default( 'new' );
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
    public function down ()
    {
        Schema::dropIfExists( 'tickets' );
    }
}
