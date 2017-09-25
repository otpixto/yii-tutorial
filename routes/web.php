<?php

Route::get( '/test', 'ProfileController@getTest' )->name( 'test' );
Route::get( '/fix/{ext_number}', 'ProfileController@getFix' )->name( 'fix' );
Route::any( '/bot/telegram/{token}', 'BotController@telegram' );
Route::post( '/rest/create-draft', 'RestController@createDraft' );

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
    Route::post( '/profile/phone-unreg', 'ProfileController@postPhoneUnreg' )->name( 'profile.phone_unreg' );

	Route::get( '/tickets/{id}/add-management', 'Operator\TicketsController@getAddManagement' )->name( 'tickets.add_management' );
    Route::post( '/tickets/{id}/add-management', 'Operator\TicketsController@postAddManagement' );
	Route::post( '/tickets/del-management', 'Operator\TicketsController@postDelManagement' )->name( 'tickets.del_management' );
    Route::get( '/tickets/rate', 'Operator\TicketsController@getRateForm' )->name( 'tickets.rate' );
    Route::post( '/tickets/rate', 'Operator\TicketsController@postRateForm' );
    Route::post( '/tickets/close', 'Operator\TicketsController@postClose' )->name( 'tickets.close' );
    Route::post( '/tickets/repeat', 'Operator\TicketsController@postRepeat' )->name( 'tickets.repeat' );
    Route::post( '/tickets/create-draft', 'Operator\TicketsController@createDraft' )->name( 'tickets.create_draft' );

    Route::post( '/tickets/save', 'Operator\TicketsController@postSave' )->name( 'tickets.save' );
    Route::get( '/tickets/{id}/cancel', 'Operator\TicketsController@cancel' )->name( 'tickets.cancel' );
    Route::get( '/tickets/call', 'Operator\TicketsController@call' )->name( 'tickets.call' );
    Route::get( '/tickets/closed', 'Operator\TicketsController@closed' )->name( 'tickets.closed' );
    Route::get( '/tickets/no_contract', 'Operator\TicketsController@no_contract' )->name( 'tickets.no_contract' );
    Route::get( '/tickets/canceled', 'Operator\TicketsController@canceled' )->name( 'tickets.canceled' );
    Route::get( '/tickets/closed', 'Operator\TicketsController@closed' )->name( 'tickets.closed' );
    Route::get( '/tickets/{id}/act', 'Operator\TicketsController@act' )->name( 'tickets.act' );
    Route::get( '/tickets/search', 'Operator\TicketsController@search' )->name( 'tickets.search' );
    Route::get( '/tickets/{customer_id}/customer_tickets', 'Operator\TicketsController@customerTickets' )->name( 'tickets.customer_tickets' );
    Route::post( '/tickets/{id}/change-status', 'Operator\TicketsController@changeStatus' )->name( 'tickets.status' );
    Route::post( '/tickets/managements/{id}/change-status', 'Operator\TicketsController@changeManagementStatus' )->name( 'tickets.managements.status' );
    Route::post( '/tickets/managements/{id}/executor', 'Operator\TicketsController@setExecutor' )->name( 'tickets.managements.executor' );
	Route::post( '/tickets/{id}/comment', 'Operator\TicketsController@comment' )->name( 'tickets.comment' );
    Route::post( '/tickets/action', 'Operator\TicketsController@action' )->name( 'tickets.action' );
    Route::post( '/tickets', 'Operator\TicketsController@export' );
    Route::resource( '/tickets', 'Operator\TicketsController' );

	Route::get( '/comment', 'CommentsController@form' )->name( 'comments.form' );
	Route::post( '/comment', 'CommentsController@store' )->name( 'comments.store' );

    Route::post( '/managements/search', 'Catalog\ManagementsController@search' )->name( 'managements.search' );
    Route::post( '/types/search', 'Catalog\TypesController@search' )->name( 'types.search' );

    Route::get( '/addresses/search', 'Catalog\AddressesController@search' )->name( 'addresses.search' );
    Route::get( '/binds/delete', 'BindsController@delete' )->name( 'binds.delete' );

    Route::get( '/customers/names', 'Catalog\CustomersController@names' )->name( 'customers.names' );
    Route::get( '/customers/search', 'Catalog\CustomersController@search' )->name( 'customers.search' );

    Route::get( '/works/search', 'Operator\WorksController@search' )->name( 'works.search' );
    Route::resource( '/works', 'Operator\WorksController' );
    Route::resource( '/schedule', 'Operator\ScheduleController' );

    Route::prefix( 'catalog' )->group( function ()
    {

        Route::post( 'addresses/types/add', 'Catalog\AddressesController@addTypes' )->name( 'addresses.types.add' );
        Route::post( 'addresses/types/del', 'Catalog\AddressesController@delType' )->name( 'addresses.types.del' );
        Route::post( 'addresses/managements/add', 'Catalog\AddressesController@addManagements' )->name( 'addresses.managements.add' );
        Route::post( 'addresses/managements/del', 'Catalog\AddressesController@delManagement' )->name( 'addresses.managements.del' );
        Route::resource( 'addresses', 'Catalog\AddressesController' );

        Route::resource( 'categories', 'Catalog\CategoriesController' );

        Route::post( 'types/addresses/add', 'Catalog\TypesController@addAddresses' )->name( 'types.addresses.add' );
        Route::post( 'types/addresses/del', 'Catalog\TypesController@delAddress' )->name( 'types.addresses.del' );
        Route::post( 'types/managements/add', 'Catalog\TypesController@addManagements' )->name( 'types.managements.add' );
        Route::post( 'types/managements/del', 'Catalog\TypesController@delManagement' )->name( 'types.managements.del' );
        Route::resource( 'types', 'Catalog\TypesController' );

        Route::post( 'managements/telegram', 'Catalog\ManagementsController@telegram' )->name( 'managements.telegram' );
        Route::post( 'managements/types/add', 'Catalog\ManagementsController@addTypes' )->name( 'managements.types.add' );
        Route::post( 'managements/types/del', 'Catalog\ManagementsController@delType' )->name( 'managements.types.del' );
        Route::post( 'managements/addresses/add', 'Catalog\ManagementsController@addAddresses' )->name( 'managements.addresses.add' );
        Route::post( 'managements/addresses/del', 'Catalog\ManagementsController@delAddress' )->name( 'managements.addresses.del' );
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
