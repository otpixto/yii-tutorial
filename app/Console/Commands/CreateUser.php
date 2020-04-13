<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class CreateUser extends Command
{

    protected $signature = 'create:user';

    protected $description = 'Создать пользователя';

    public function __construct ()
    {
        parent::__construct();
    }

    public function handle ()
    {

        $email = $this->ask('Email' );

        $user = User::where( 'email', '=', $email )->first();

        if ( $user )
        {
            if ( ! $this->confirm('User with email "' . $user->email . '" found. Do you wish to continue and reset password?' ) )
            {
                return;
            }
        }

        $password = $this->secret('Password' );
        $user = new User();
        $user->password = \Hash::make( $password );
        $user->email = $email;
        $user->active = 1;
        $user->save();

    }

}