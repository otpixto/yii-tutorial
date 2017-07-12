<?php

Route::post( '/addresses/search', 'Operator\AddressController@search' )->name( 'address.search' );

Route::group( [ 'middleware' => 'auth' ], function ()
{

    Route::get( '/', 'HomeController@getIndex' )->name( 'home' );
    Route::resource( 'tickets', 'Operator\TicketsController' );

    Route::get( 'addresses/search', 'Catalog\AddressesController@search' )->name( 'address.search' );
    Route::get( 'customers/names', 'Catalog\CustomersController@names' )->name( 'customer.names' );
    Route::get( 'customers/search', 'Catalog\CustomersController@search' )->name( 'customer.search' );

    Route::prefix( 'catalog' )->group( function ()
    {

        Route::resource( 'addresses', 'Catalog\AddressesController' );
        Route::resource( 'categories', 'Catalog\CategoriesController' );
        Route::resource( 'types', 'Catalog\TypesController' );
        Route::resource( 'managements', 'Catalog\ManagementsController' );
        Route::resource( 'customers', 'Catalog\CustomersController' );

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
