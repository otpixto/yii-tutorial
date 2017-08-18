<?php

Route::get( '/test', 'ProfileController@getTest' )->name( 'test' );

Route::group( [ 'middleware' => 'auth' ], function ()
{

    Route::get( '/', 'HomeController@index' )->name( 'home' );
    Route::get( '/about', 'HomeController@about' )->name( 'about' );

    Route::get( '/files/download', 'FilesController@download' )->name( 'files.download' );

    Route::get( '/profile/phone', 'ProfileController@getPhone' )->name( 'profile.phone' );
    Route::get( '/profile/phone-reg', 'ProfileController@getPhoneReg' )->name( 'profile.phone_reg' );
    Route::post( '/profile/phone-reg', 'ProfileController@postPhoneReg' );
    Route::get( '/profile/phone-confirm', 'ProfileController@getPhoneConfirm' )->name( 'profile.phone_confirm' );
    Route::post( '/profile/phone-confirm', 'ProfileController@postPhoneConfirm' );
    Route::get( '/profile/phone-unreg', 'ProfileController@getPhoneUnreg' )->name( 'profile.phone_unreg' );

    Route::get( 'tickets/rate', 'Operator\TicketsController@getRateForm' )->name( 'tickets.rate' );
    Route::post( 'tickets/rate', 'Operator\TicketsController@postRateForm' );
    Route::post( 'tickets/close', 'Operator\TicketsController@postClose' )->name( 'tickets.close' );
    Route::post( 'tickets/repeat', 'Operator\TicketsController@postRepeat' )->name( 'tickets.repeat' );

    Route::get( 'tickets/call', 'Operator\TicketsController@call' )->name( 'tickets.call' );
    Route::get( 'tickets/{id}/act', 'Operator\TicketsController@act' )->name( 'tickets.act' );
    Route::post( 'tickets/{id}/change-status', 'Operator\TicketsController@changeStatus' )->name( 'tickets.status' );
    Route::post( 'tickets/managements/{id}/change-status', 'Operator\TicketsController@changeManagementStatus' )->name( 'tickets.managements.status' );
    Route::post( 'tickets/managements/{id}/executor', 'Operator\TicketsController@setExecutor' )->name( 'tickets.managements.executor' );
	Route::post( 'tickets/{id}/comment', 'Operator\TicketsController@comment' )->name( 'tickets.comment' );
    Route::post( 'tickets/action', 'Operator\TicketsController@action' )->name( 'tickets.action' );
    Route::resource( 'tickets', 'Operator\TicketsController' );

	Route::get( 'comment', 'CommentsController@form' )->name( 'comments.form' );
	Route::post( 'comment', 'CommentsController@store' )->name( 'comments.store' );

    Route::post( 'managements/search', 'Catalog\ManagementsController@search' )->name( 'managements.search' );
    Route::post( 'types/search', 'Catalog\TypesController@search' )->name( 'types.search' );

    Route::get( 'addresses/search', 'Catalog\AddressesController@search' )->name( 'addresses.search' );
    Route::get( 'binds/delete', 'BindsController@delete' )->name( 'binds.delete' );

    Route::get( 'customers/names', 'Catalog\CustomersController@names' )->name( 'customers.names' );
    Route::get( 'customers/search', 'Catalog\CustomersController@search' )->name( 'customers.search' );

    Route::resource( 'works', 'Operator\WorksController' );
    Route::resource( 'schedule', 'Operator\ScheduleController' );

    Route::prefix( 'catalog' )->group( function ()
    {

        Route::resource( 'addresses', 'Catalog\AddressesController' );
        Route::post( 'addresses/types/add', 'Catalog\AddressesController@addTypes' )->name( 'addresses.types.add' );
        Route::post( 'addresses/managements/add', 'Catalog\AddressesController@addManagements' )->name( 'addresses.managements.add' );

        Route::resource( 'categories', 'Catalog\CategoriesController' );
        Route::resource( 'types', 'Catalog\TypesController' );
        Route::resource( 'managements', 'Catalog\ManagementsController' );
        Route::post( 'managements/addresses/add', 'Catalog\ManagementsController@addAddresses' )->name( 'managements.addresses.add' );

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
