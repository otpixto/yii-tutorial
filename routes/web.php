<?php

Route::group( [ 'middleware' => 'auth' ], function ()
{

    Route::get( '/', 'HomeController@getIndex' )->name( 'home' );

    Route::prefix( 'admin' )->group( function ()
    {

        Route::get( '/', 'AdminController@getIndex' )->name( 'admin' );

        Route::resource( 'users', 'Admin\UsersController' );
        Route::resource( 'roles', 'Admin\RoleController' );
        Route::resource( 'perms', 'Admin\PermsController' );

    });

});

Auth::routes();
