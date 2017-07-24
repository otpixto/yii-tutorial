<?php

Route::group( [ 'middleware' => 'auth' ], function ()
{

    Route::get( '/', 'HomeController@getIndex' )->name( 'home' );
    Route::resource( 'tickets', 'Operator\TicketsController' );
	Route::post( 'tickets/{id}/comment', 'Operator\TicketsController@comment' )->name( 'tickets.comment' );
	
	Route::get( 'comment', 'CommentsController@form' )->name( 'comments.form' );
	Route::post( 'comment', 'CommentsController@store' )->name( 'comments.store' );

    Route::post( 'managements/search', 'Catalog\ManagementsController@search' )->name( 'managements.search' );

    Route::get( 'addresses/search', 'Catalog\AddressesController@search' )->name( 'addresses.search' );
    Route::get( 'customers/names', 'Catalog\CustomersController@names' )->name( 'customers.names' );
    Route::get( 'customers/search', 'Catalog\CustomersController@search' )->name( 'customers.search' );

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
