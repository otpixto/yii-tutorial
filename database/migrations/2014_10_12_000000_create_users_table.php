<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
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

            Schema::create( 'users', function ( Blueprint $table )
            {
                $table->increments('id' );
                $table->string('firstname' )->nullable();
                $table->string('middlename' )->nullable();
                $table->string('lastname' )->nullable();
                $table->string('phone' )->nullable();
                $table->string('email' )->unique();
                $table->string('password' );
                $table->boolean( 'active' )->default( 0 );
                $table->boolean( 'admin' )->default( 0 );
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });

            $user = \App\User::create([
                'email'         => 'admin@ip-home.net',
                'password'      => bcrypt('admin' ),
            ]);

            $user->active = 1;
            $user->admin = 1;
            $user->save();

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
        Schema::dropIfExists( 'users' );
    }
}
