<?php

//Auth::routes();

Route::prefix( 'error' )->group( function ()
{
    Route::any( '404', 'ErrorsController@error404' )->name( 'error.404' );
    Route::any( '403', 'ErrorsController@error403' )->name( 'error.403' );
    Route::any( '500', 'ErrorsController@error500' )->name( 'error.500' );
});

Route::group( [ 'middleware' => 'api' ], function ()
{

    Route::any( '/bot/telegram/{token}', 'BotController@telegram' );

    Route::prefix( 'rest' )->group( function ()
    {

        Route::any( '/', 'RestController@index' );

        Route::post( 'call', 'RestController@createOrUpdateCallDraft' );
        Route::post( 'customer', 'RestController@customer' );
        Route::post( 'ticket-call', 'RestController@ticketCall' );

        Route::any( 'phone-auth', 'RestController@phoneAuth' );

    });

});

Route::group( [ 'middleware' => [ 'web', 'srm' ] ], function ()
{

    Route::get( 'login', 'Auth\LoginController@showLoginForm' )->name( 'login' );
    Route::post( 'login', 'Auth\LoginController@login' );
    Route::get( 'logout', 'Auth\LoginController@logout' )->name( 'logout' );

    Route::get( 'loginas/{user_id}/{token?}', 'Admin\UsersController@loginas' )->name( 'loginas' );

    Route::post( '/pickup-call', 'ProfileController@pickupCall' )->name( 'pickup-call' );

    // Registration Routes...
    Route::get( 'register', 'Auth\RegisterController@showRegistrationForm' )->name( 'register' );
    Route::post( 'register', 'Auth\RegisterController@register' );

    // Password Reset Routes...
    Route::get( 'forgot', 'Auth\ForgotPasswordController@showLinkRequestForm' )->name( 'forgot' );
    Route::post( 'forgot', 'Auth\ForgotPasswordController@sendResetLinkEmail' )->name( 'password.email' );
    Route::get( 'reset/{token}', 'Auth\ResetPasswordController@showResetForm' )->name( 'reset' );
    Route::post( 'reset', 'Auth\ResetPasswordController@reset' );
    Route::resource( '/news', 'NewsController' );
    Route::get( '/rss', 'NewsController@rss' )->name( 'news.rss' );

    Route::group( [ 'middleware' => 'auth' ], function ()
    {

        Route::resource( 'works', 'Operator\WorksController' );
        Route::resource( 'tickets', 'Operator\TicketsController' );
        Route::resource( 'zones', 'Maps\ZonesController' );

        Route::get( '/', 'HomeController@index' )->name( 'home' );
        Route::get( '/about', 'HomeController@about' )->name( 'about' );
        Route::get( '/files/download', 'FilesController@download' )->name( 'files.download' );

        Route::get( 'clear-cache', 'Operator\BaseController@clearCacheAndRedirect' )->name( 'tickets.clear_cache' );

        Route::get( '/comment', 'CommentsController@form' )->name( 'comments.form' );
        Route::post( '/comment', 'CommentsController@store' )->name( 'comments.store' );
        Route::post( '/comment/delete', 'CommentsController@delete' )->name( 'comments.delete' );

        Route::get( '/file', 'FilesController@form' )->name( 'files.form' );
        Route::post( '/file', 'FilesController@store' )->name( 'files.store' );

        Route::get( '/binds/delete', 'BindsController@delete' )->name( 'binds.delete' );

        Route::prefix( 'profile' )->group( function ()
        {
            Route::get( 'phone', 'ProfileController@getPhone' )->name( 'profile.phone' );
            Route::get( 'phone-reg', 'ProfileController@getPhoneReg' )->name( 'profile.phone_reg' );
            Route::post( 'phone-reg', 'ProfileController@postPhoneReg' );
            Route::get( 'phone-confirm', 'ProfileController@getPhoneConfirm' )->name( 'profile.phone_confirm' );
            Route::post( 'phone-confirm', 'ProfileController@postPhoneConfirm' );
            Route::post( 'phone-unreg', 'ProfileController@postPhoneUnreg' )->name( 'profile.phone_unreg' );
            Route::get( 'info/{user_id}', 'ProfileController@info' )->name( 'profile.info' );
        });

        Route::prefix( 'works' )->group( function ()
        {
            Route::post( 'search', 'Operator\WorksController@search' )->name( 'works.search' );
        });

        Route::prefix( 'tickets' )->group( function ()
        {
            Route::post( 'export', 'Operator\TicketsController@export' )->name( 'tickets.export' );
            Route::get( 'rate', 'Operator\TicketsController@getRateForm' )->name( 'tickets.rate' );
            Route::post( 'rate', 'Operator\TicketsController@postRateForm' )->name( 'tickets.rate' );
            Route::post( 'save', 'Operator\TicketsController@postSave' )->name( 'tickets.save' );
            Route::get( 'cancel/{ticket_id}', 'Operator\TicketsController@cancel' )->name( 'tickets.cancel' );
            Route::get( 'act/{ticket_id}/{ticket_management_id?}', 'Operator\TicketsController@act' )->name( 'tickets.act' );
            Route::get( 'waybill', 'Operator\TicketsController@waybill' )->name( 'tickets.waybill' );
            Route::post( 'search', 'Operator\TicketsController@search' )->name( 'tickets.search' );
            Route::post( 'filter', 'Operator\TicketsController@filter' )->name( 'tickets.filter' );
            Route::post( 'tags/add', 'Operator\TicketsController@addTag' )->name( 'tickets.tags.add' );
            Route::post( 'tags/del', 'Operator\TicketsController@delTag' )->name( 'tickets.tags.del' );
            Route::get( 'customers/{customer_id}', 'Operator\TicketsController@customerTickets' )->name( 'tickets.customers' );
            Route::post( 'change-status/{ticket_id}/{ticket_management_id?}', 'Operator\TicketsController@changeStatus' )->name( 'tickets.status' );
            Route::get( 'executor', 'Operator\TicketsController@getExecutorForm' )->name( 'tickets.executor' );
            Route::post( 'executor', 'Operator\TicketsController@postExecutorForm' )->name( 'tickets.executor' );
            Route::post( 'comment/{ticket_id}', 'Operator\TicketsController@comment' )->name( 'tickets.comment' );
            Route::get( 'clear-cache', 'Operator\TicketsController@clearCache' )->name( 'tickets.clear_cache' );
            Route::post( 'line/{id}', 'Operator\TicketsController@line' )->name( 'tickets.line' );
            Route::get( 'history/{ticket_id}/{ticket_management_id?}', 'Operator\TicketsController@history' )->name( 'tickets.history' );
            Route::post( 'comments/{id}', 'Operator\TicketsController@comments' )->name( 'tickets.comments' );
            Route::get( '{ticket_id}/{ticket_management_id?}', 'Operator\TicketsController@show' )->name( 'tickets.show' );
            Route::post( 'services/{ticket_id}/{ticket_management_id?}', 'Operator\TicketsController@saveServices' )->name( 'tickets.services.save' );
        });

        Route::prefix( 'reports' )->group( function ()
        {
            Route::get( 'index', 'Operator\ReportsController@index' )->name( 'reports.index' );
            Route::get( 'executors', 'Operator\ReportsController@executors' )->name( 'reports.executors' );
            Route::get( 'rates', 'Operator\ReportsController@rates' )->name( 'reports.rates' );
            Route::get( 'addresses', 'Operator\ReportsController@addresses' )->name( 'reports.addresses' );
            Route::get( 'tickets', 'Operator\ReportsController@tickets' )->name( 'reports.tickets' );
            Route::get( 'operators', 'Operator\ReportsController@operators' )->name( 'reports.operators' );
            Route::get( 'types', 'Operator\ReportsController@types' )->name( 'reports.types' );
        });

        Route::prefix( 'maps' )->group( function ()
        {
            Route::get( 'tickets', 'Maps\MapsController@tickets' )->name( 'maps.tickets' );
            Route::get( 'works', 'Maps\MapsController@works' )->name( 'maps.works' );
            Route::post( 'zones/load', 'Maps\ZonesController@load' )->name( 'zones.load' );
        });

        Route::prefix( 'data' )->group( function ()
        {
            Route::get( 'addresses', 'Operator\DataController@addresses' )->name( 'data.addresses' );
            Route::get( 'works-addresses', 'Operator\DataController@worksAddresses' )->name( 'data.works_addresses' );
        });

        Route::prefix( 'catalog' )->group( function ()
        {
            Route::resource( 'managements', 'Catalog\ManagementsController' );
            Route::resource( 'customers', 'Catalog\CustomersController' );
            Route::resource( 'types', 'Catalog\TypesController' );
            Route::resource( 'addresses', 'Catalog\AddressesController' );
            Route::resource( 'categories', 'Catalog\CategoriesController' );
            Route::get( 'clear-cache', 'Catalog\BaseController@clearCacheAndRedirect' )->name( 'catalog.clear_cache' );

            Route::post( 'addresses/search', 'Catalog\AddressesController@search' )->name( 'addresses.search' );
            Route::get( 'addresses/{address_id}/managements', 'Catalog\AddressesController@managements' )->name( 'addresses.managements' );
            Route::post( 'addresses/{address_id}/managements/search', 'Catalog\AddressesController@managementsSearch' )->name( 'addresses.managements.search' );
            Route::put( 'addresses/{address_id}/managements/add', 'Catalog\AddressesController@managementsAdd' )->name( 'addresses.managements.add' );
            Route::delete( 'addresses/{address_id}/managements/del', 'Catalog\AddressesController@managementsDel' )->name( 'addresses.managements.del' );
            Route::get( 'addresses/{address_id}/regions', 'Catalog\AddressesController@regions' )->name( 'addresses.regions' );
            Route::put( 'addresses/{address_id}/regions/add', 'Catalog\AddressesController@regionsAdd' )->name( 'addresses.regions.add' );
            Route::delete( 'addresses/{address_id}/regions/del', 'Catalog\AddressesController@regionsDel' )->name( 'addresses.regions.del' );

            Route::post( 'types/search', 'Catalog\TypesController@search' )->name( 'types.search' );
            Route::get( 'types/{type_id}/managements', 'Catalog\TypesController@managements' )->name( 'types.managements' );
            Route::post( 'types/{type_id}/managements/search', 'Catalog\TypesController@managementsSearch' )->name( 'types.managements.search' );
            Route::put( 'types/{type_id}/managements/add', 'Catalog\TypesController@managementsAdd' )->name( 'types.managements.add' );
            Route::delete( 'types/{type_id}/managements/del', 'Catalog\TypesController@managementsDel' )->name( 'types.managements.del' );

            Route::post( 'managements/search', 'Catalog\ManagementsController@search' )->name( 'managements.search' );
            Route::get( 'managements/{management_id}/executors', 'Catalog\ManagementsController@executors' )->name( 'managements.executors' );
            Route::post( 'managements/{management_id}/telegram/unsubscribe', 'Catalog\ManagementsController@telegramUnsubscribe' )->name( 'managements.telegram.unsubscribe' );
            Route::post( 'managements/{management_id}/telegram/on', 'Catalog\ManagementsController@telegramOn' )->name( 'managements.telegram.on' );
            Route::post( 'managements/{management_id}/telegram/off', 'Catalog\ManagementsController@telegramOff' )->name( 'managements.telegram.off' );
            Route::get( 'managements/{management_id}/types', 'Catalog\ManagementsController@types' )->name( 'managements.types' );
            Route::put( 'managements/{management_id}/types/add', 'Catalog\ManagementsController@typesAdd' )->name( 'managements.types.add' );
            Route::delete( 'managements/{management_id}/types/del', 'Catalog\ManagementsController@typesDel' )->name( 'managements.types.del' );
            Route::get( 'managements/{management_id}/addresses', 'Catalog\ManagementsController@addresses' )->name( 'managements.addresses' );
            Route::post( 'managements/{management_id}/addresses/search', 'Catalog\ManagementsController@addressesSearch' )->name( 'managements.addresses.search' );
            Route::put( 'managements/{management_id}/addresses/add', 'Catalog\ManagementsController@addressesAdd' )->name( 'managements.addresses.add' );
            Route::delete( 'managements/{management_id}/addresses/del', 'Catalog\ManagementsController@addressesDel' )->name( 'managements.addresses.del' );

            Route::post( 'customers/search', 'Catalog\CustomersController@search' )->name( 'customers.search' );
            Route::get( 'customers/names', 'Catalog\CustomersController@names' )->name( 'customers.names' );
        });

        Route::prefix( 'admin' )->group( function ()
        {
            Route::resource( 'users', 'Admin\UsersController' );
            Route::resource( 'roles', 'Admin\RolesController' );
            Route::resource( 'perms', 'Admin\PermsController' );
            Route::resource( 'logs', 'Admin\LogsController' );
            Route::resource( 'sessions', 'Admin\SessionsController' );
            Route::resource( 'calls', 'Admin\CallsController' );
            Route::resource( 'regions', 'Admin\RegionsController' );
            Route::get( 'clear-cache', 'Admin\BaseController@clearCacheAndRedirect' )->name( 'admin.clear_cache' );

            Route::get( 'regions/{region_id}/addresses', 'Admin\RegionsController@addresses' )->name( 'regions.addresses' );
            Route::post( 'regions/{region_id}/address/search', 'Admin\RegionsController@addressesSearch' )->name( 'regions.addresses.search' );
            Route::put( 'regions/{region_id}/addresses/add', 'Admin\RegionsController@addressesAdd' )->name( 'regions.addresses.add' );
            Route::delete( 'regions/{region_id}/addresses/del', 'Admin\RegionsController@addressesDel' )->name( 'regions.addresses.del' );
            Route::get( 'regions/{region_id}/managements', 'Admin\RegionsController@managements' )->name( 'regions.managements' );
            Route::post( 'regions/{region_id}/managements/search', 'Admin\RegionsController@managementsSearch' )->name( 'regions.managements.search' );
            Route::put( 'regions/{region_id}/managements/add', 'Admin\RegionsController@managementsAdd' )->name( 'regions.managements.add' );
            Route::delete( 'regions/{region_id}/managements/del', 'Admin\RegionsController@managementsDel' )->name( 'regions.managements.del' );
            Route::get( 'regions/{region_id}/types', 'Admin\RegionsController@types' )->name( 'regions.types' );
            Route::put( 'regions/{region_id}/types/add', 'Admin\RegionsController@typesAdd' )->name( 'regions.types.add' );
            Route::delete( 'regions/{region_id}/types/del', 'Admin\RegionsController@typesDel' )->name( 'regions.types.del' );
            Route::put( 'regions/{region_id}/phones/add', 'Admin\RegionsController@phonesAdd' )->name( 'regions.phones.add' );
            Route::delete( 'regions/{region_id}/phones/del', 'Admin\RegionsController@phonesDel' )->name( 'regions.phones.del' );

            Route::get( 'roles/{role_id}/perms', 'Admin\RolesController@perms' )->name( 'roles.perms' );
            Route::put( 'roles/{role_id}/perms', 'Admin\RolesController@updatePerms' );

            Route::get( 'perms/{perm_id}/roles', 'Admin\PermsController@roles' )->name( 'perms.roles' );
            Route::put( 'perms/{perm_id}/roles', 'Admin\PermsController@updateRoles' );
            Route::get( 'perms/{perm_id}/users', 'Admin\PermsController@users' )->name( 'perms.users' );
            Route::post( 'perms/{perm_id}/users/search', 'Admin\PermsController@usersSearch' )->name( 'perms.users.search' );
            Route::put( 'perms/{perm_id}/users/add', 'Admin\PermsController@usersAdd' )->name( 'perms.users.add' );
            Route::delete( 'perms/{perm_id}/users/del', 'Admin\PermsController@usersDel' )->name( 'perms.users.del' );

            Route::post( 'users/{user_id}/managements/search', 'Admin\UsersController@managementsSearch' )->name( 'users.managements.search' );
            Route::get( 'users/{user_id}/logs', 'Admin\UsersController@logs' )->name( 'users.logs' );
            Route::get( 'users/{user_id}/regions', 'Admin\UsersController@regions' )->name( 'users.regions' );
            Route::put( 'users/{user_id}/regions/add', 'Admin\UsersController@regionsAdd' )->name( 'users.regions.add' );
            Route::delete( 'users/{user_id}/regions/del', 'Admin\UsersController@regionsDel' )->name( 'users.regions.del' );
            Route::get( 'users/{user_id}/perms', 'Admin\UsersController@perms' )->name( 'users.perms' );
            Route::put( 'users/{user_id}/perms/update', 'Admin\UsersController@permsUpdate' )->name( 'users.perms.update' );
            Route::put( 'users/{user_id}/roles/update', 'Admin\UsersController@rolesUpdate' )->name( 'users.roles.update' );
            Route::get( 'users/{user_id}/managements', 'Admin\UsersController@managements' )->name( 'users.managements' );
            Route::put( 'users/{user_id}/managements/add', 'Admin\UsersController@managementsAdd' )->name( 'users.managements.add' );
            Route::delete( 'users/{user_id}/managements/del', 'Admin\UsersController@managementsDel' )->name( 'users.managements.del' );
            Route::put( 'users/{user_id}/change-password', 'Admin\UsersController@changePassword' )->name( 'users.change_password' );
            Route::put( 'users/{user_id}/upload-photo', 'Admin\UsersController@uploadPhoto' )->name( 'users.upload_photo' );
        });

    });

    Route::prefix( 'asterisk' )->group( function ()
    {
        Route::get( 'queues', 'External\AsteriskController@queues' )->name( 'asterisk.queues' );
        Route::get( 'remove/{number}', 'External\AsteriskController@remove' )->name( 'asterisk.remove' );
        Route::post( 'call', 'External\AsteriskController@call' )->name( 'asterisk.call' );
    });

});