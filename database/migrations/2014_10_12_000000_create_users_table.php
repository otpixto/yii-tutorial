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

        Schema::create( 'users', function ( Blueprint $table )
        {
            $table->increments('id' );
            $table->string('firstname' );
            $table->string('middlename' );
            $table->string('lastname' );
            $table->string('phone' );
            $table->string('email' )->unique();
            $table->string('password' );
            $table->boolean( 'activated' )->default( 0 );
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        \App\User::create([
            'email'         => 'dima@ip-home.net',
            'password'      => bcrypt('Babuin88' ),
            'firstname'     => 'Дмитрий',
            'middlename'    => 'Сергеевич',
            'lastname'      => 'Скабелин',
            'phone'         => '79646279122',
            'activated'     => 1
        ]);

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
