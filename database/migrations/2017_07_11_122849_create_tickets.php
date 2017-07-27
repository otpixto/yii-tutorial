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
                $table->integer('address_id' )->unsigned();
                $table->integer('customer_id' )->unsigned();
                $table->string('firstname' );
                $table->string('middlename' )->nullable();
                $table->string('lastname' )->nullable();
                $table->string('phone' );
                $table->string('phone2' )->nullable();
                $table->string('text' );
                $table->enum( 'status', [ 'draft','accepted_operator','perform','accepted_management','done','done_without_act','closed_success','closed_without_confirm','not_confirmed','not_done','cancel','failure' ] )->default( 'draft' );
                $table->string('group_uuid' )->nullable();
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
