<?php

Route::post( '/addresses/search', 'Operator\AddressController@search' )->name( 'address.search' );

Route::group( [ 'middleware' => 'auth' ], function ()
{

    Route::get( '/', 'HomeController@getIndex' )->name( 'home' );
    Route::resource( 'tickets', 'Operator\TicketsController' );

    Route::prefix( 'catalog' )->group( function ()
    {

        Route::resource( 'categories', 'Catalog\CategoriesController' );
        Route::resource( 'types', 'Catalog\TypesController' );

    });

    Route::prefix( 'admin' )->group( function ()
    {

        //Route::get( '/', 'AdminController@getIndex' )->name( 'admin' );

        Route::resource( 'users', 'Admin\UsersController' );
        Route::resource( 'roles', 'Admin\RolesController' );
        Route::resource( 'perms', 'Admin\PermsController' );

    });

});

Auth::routes();
