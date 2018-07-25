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

        Route::get( '/', 'HomeController@index' )->name( 'home' );
        Route::get( '/about', 'HomeController@about' )->name( 'about' );
        Route::get( '/files/download', 'FilesController@download' )->name( 'files.download' );

        Route::get( '/blank', 'HomeController@blank' )->name( 'blank' );

        Route::get( 'clear-cache', 'Operator\BaseController@clearCacheAndRedirect' )->name( 'tickets.clear_cache' );

        Route::get( '/comment', 'CommentsController@form' )->name( 'comments.form' );
        Route::post( '/comment', 'CommentsController@store' )->name( 'comments.store' );
        Route::post( '/comment/delete', 'CommentsController@delete' )->name( 'comments.delete' );

        Route::get( '/file', 'FilesController@form' )->name( 'files.form' );
        Route::post( '/file', 'FilesController@store' )->name( 'files.store' );

        Route::get( '/binds/delete', 'BindsController@delete' )->name( 'binds.delete' );

        Route::get( 'loginas/{user_id}', 'ProfileController@loginas' )->name( 'loginas' );

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
            Route::post( 'filter', 'Operator\WorksController@filter' )->name( 'works.filter' );
        });

        Route::prefix( 'tickets' )->group( function ()
        {

            Route::get( 'calendar/{date}', 'Operator\TicketsController@calendar' )->name( 'tickets.calendar' );
            Route::post( 'calendar', 'Operator\TicketsController@calendarData' )->name( 'tickets.calendar_data' );

            Route::get( '{ticket_id}/neighbors', 'Operator\TicketsController@neighborsTickets' )->name( 'tickets.neighbors' );

            Route::get( '{ticket_id}/neighbors', 'Operator\TicketsController@neighborsTickets' )->name( 'tickets.neighbors' );
            Route::get( '{ticket_id}/customers', 'Operator\TicketsController@customersTickets' )->name( 'tickets.customers' );

            Route::get( '{ticket_id}/postpone', 'Operator\TicketsController@postpone' )->name( 'tickets.postpone' );

            Route::post( 'export', 'Operator\TicketsController@export' )->name( 'tickets.export' );
            Route::get( 'rate/{ticket_management_id}', 'Operator\TicketsController@getRateForm' )->name( 'tickets.rate' );
            Route::post( 'rate/{ticket_management_id}', 'Operator\TicketsController@postRateForm' )->name( 'tickets.rate' );
            Route::post( 'save', 'Operator\TicketsController@postSave' )->name( 'tickets.save' );
            Route::get( 'cancel/{ticket_id}', 'Operator\TicketsController@cancel' )->name( 'tickets.cancel' );
            Route::get( 'print/act/{ticket_id}/{ticket_management_id?}', 'Operator\TicketsController@act' )->name( 'tickets.act' );
            Route::get( 'print/waybill', 'Operator\TicketsController@waybill' )->name( 'tickets.waybill' );
            Route::get( 'search/form', 'Operator\TicketsController@searchForm' )->name( 'tickets.search.form' );
            Route::post( 'search', 'Operator\TicketsController@search' )->name( 'tickets.search' );
            Route::post( 'filter', 'Operator\TicketsController@filter' )->name( 'tickets.filter' );
            Route::post( 'tags/add', 'Operator\TicketsController@addTag' )->name( 'tickets.tags.add' );
            Route::post( 'tags/del', 'Operator\TicketsController@delTag' )->name( 'tickets.tags.del' );
            Route::post( 'change-status/{ticket_id}/{ticket_management_id?}', 'Operator\TicketsController@changeStatus' )->name( 'tickets.status' );
            Route::get( 'executor/{ticket_management_id}', 'Operator\TicketsController@getExecutorForm' )->name( 'tickets.executor' );
            Route::post( 'executor/{ticket_management_id}', 'Operator\TicketsController@postExecutorForm' )->name( 'tickets.executor' );
            Route::post( 'comment/{ticket_id}', 'Operator\TicketsController@comment' )->name( 'tickets.comment' );
            Route::get( 'clear-cache', 'Operator\TicketsController@clearCache' )->name( 'tickets.clear_cache' );
            Route::post( 'line/{id}', 'Operator\TicketsController@line' )->name( 'tickets.line' );
            Route::get( 'history/{ticket_id}/{ticket_management_id?}', 'Operator\TicketsController@history' )->name( 'tickets.history' );
            Route::post( 'comments/{id}', 'Operator\TicketsController@comments' )->name( 'tickets.comments' );
            Route::get( '{ticket_id}/{ticket_management_id?}', 'Operator\TicketsController@show' )->name( 'tickets.show' );
            Route::put( 'services/{ticket_management_id}', 'Operator\TicketsController@saveServices' )->name( 'tickets.services.save' );

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
            Route::resource( 'zones', 'Maps\ZonesController' );
            Route::get( 'tickets', 'Maps\MapsController@tickets' )->name( 'maps.tickets' );
            Route::get( 'works', 'Maps\MapsController@works' )->name( 'maps.works' );
            Route::post( 'zones/load', 'Maps\ZonesController@load' )->name( 'zones.load' );
        });

        Route::prefix( 'data' )->group( function ()
        {
            Route::get( 'buildings', 'Operator\DataController@buildings' )->name( 'data.buildings' );
            Route::get( 'works-buildings', 'Operator\DataController@worksBuildings' )->name( 'data.works_buildings' );
        });

        Route::prefix( 'catalog' )->group( function ()
        {

            Route::get( 'segments/tree', 'Catalog\SegmentsController@tree' )->name( 'segments.tree' );

            Route::resource( 'users', 'Catalog\UsersController' );
            Route::resource( 'managements', 'Catalog\ManagementsController' );
            Route::resource( 'executors', 'Catalog\ExecutorsController' );
            Route::resource( 'customers', 'Catalog\CustomersController' );
            Route::resource( 'types', 'Catalog\TypesController' );
            Route::resource( 'segments', 'Catalog\SegmentsController' );
            Route::resource( 'buildings', 'Catalog\BuildingsController' );
            Route::resource( 'categories', 'Catalog\CategoriesController' );

            Route::get( 'clear-cache', 'Catalog\BaseController@clearCacheAndRedirect' )->name( 'catalog.clear_cache' );

            Route::post( 'segments/search', 'Catalog\SegmentsController@search' )->name( 'segments.search' );

            Route::post( 'buildings/search', 'Catalog\BuildingsController@search' )->name( 'buildings.search' );
            Route::get( 'buildings/{building_id}/managements', 'Catalog\BuildingsController@managements' )->name( 'buildings.managements' );
            Route::post( 'buildings/{building_id}/managements/search', 'Catalog\BuildingsController@managementsSearch' )->name( 'buildings.managements.search' );
            Route::put( 'buildings/{building_id}/managements/add', 'Catalog\BuildingsController@managementsAdd' )->name( 'buildings.managements.add' );
            Route::delete( 'buildings/{building_id}/managements/del', 'Catalog\BuildingsController@managementsDel' )->name( 'buildings.managements.del' );
            Route::delete( 'buildings/{building_id}/managements/empty', 'Catalog\BuildingsController@managementsEmpty' )->name( 'buildings.managements.empty' );
            Route::get( 'buildings/{building_id}/providers', 'Catalog\BuildingsController@providers' )->name( 'buildings.providers' );
            Route::put( 'buildings/{building_id}/providers/add', 'Catalog\BuildingsController@providersAdd' )->name( 'buildings.providers.add' );
            Route::delete( 'buildings/{building_id}/providers/del', 'Catalog\BuildingsController@providersDel' )->name( 'buildings.providers.del' );
            Route::delete( 'buildings/{building_id}/providers/empty', 'Catalog\BuildingsController@providersEmpty' )->name( 'buildings.providers.empty' );

            Route::post( 'types/search', 'Catalog\TypesController@search' )->name( 'types.search' );
            Route::get( 'types/{type_id}/managements', 'Catalog\TypesController@managements' )->name( 'types.managements' );
            Route::post( 'types/{type_id}/managements/search', 'Catalog\TypesController@managementsSearch' )->name( 'types.managements.search' );
            Route::put( 'types/{type_id}/managements/add', 'Catalog\TypesController@managementsAdd' )->name( 'types.managements.add' );
            Route::delete( 'types/{type_id}/managements/del', 'Catalog\TypesController@managementsDel' )->name( 'types.managements.del' );
            Route::delete( 'types/{type_id}/managements/empty', 'Catalog\TypesController@managementsEmpty' )->name( 'types.managements.empty' );

            Route::post( 'managements/search', 'Catalog\ManagementsController@search' )->name( 'managements.search' );
            Route::post( 'managements/{management_id}/parents/search', 'Catalog\ManagementsController@parentsSearch' )->name( 'managements.parents.search' );
            Route::post( 'managements/{management_id}/telegram/unsubscribe', 'Catalog\ManagementsController@telegramUnsubscribe' )->name( 'managements.telegram.unsubscribe' );
            Route::post( 'managements/{management_id}/telegram/on', 'Catalog\ManagementsController@telegramOn' )->name( 'managements.telegram.on' );
            Route::post( 'managements/{management_id}/telegram/off', 'Catalog\ManagementsController@telegramOff' )->name( 'managements.telegram.off' );
            Route::get( 'managements/{management_id}/types', 'Catalog\ManagementsController@types' )->name( 'managements.types' );
            Route::put( 'managements/{management_id}/types/add', 'Catalog\ManagementsController@typesAdd' )->name( 'managements.types.add' );
            Route::delete( 'managements/{management_id}/types/del', 'Catalog\ManagementsController@typesDel' )->name( 'managements.types.del' );
            Route::delete( 'managements/{management_id}/types/empty', 'Catalog\ManagementsController@typesEmpty' )->name( 'managements.types.empty' );
            Route::get( 'managements/{management_id}/buildings', 'Catalog\ManagementsController@buildings' )->name( 'managements.buildings' );
            Route::post( 'managements/{management_id}/buildings/search', 'Catalog\ManagementsController@buildingsSearch' )->name( 'managements.buildings.search' );
            Route::put( 'managements/{management_id}/buildings/add', 'Catalog\ManagementsController@buildingsAdd' )->name( 'managements.buildings.add' );
            Route::put( 'managements/{management_id}/segments/add', 'Catalog\ManagementsController@segmentsAdd' )->name( 'managements.segments.add' );
            Route::delete( 'managements/{management_id}/buildings/del', 'Catalog\ManagementsController@buildingsDel' )->name( 'managements.buildings.del' );
            Route::delete( 'managements/{management_id}/buildings/empty', 'Catalog\ManagementsController@buildingsEmpty' )->name( 'managements.buildings.empty' );
            Route::get( 'managements/{management_id}/executors', 'Catalog\ManagementsController@executors' )->name( 'managements.executors' );
            Route::post( 'managements/{management_id}/executors/search', 'Catalog\ManagementsController@executorsSearch' )->name( 'managements.executors.search' );
            Route::put( 'managements/{management_id}/executors/add', 'Catalog\ManagementsController@executorsAdd' )->name( 'managements.executors.add' );
            Route::delete( 'managements/{management_id}/executors/del', 'Catalog\ManagementsController@executorsDel' )->name( 'managements.executors.del' );
            Route::delete( 'managements/{management_id}/executors/empty', 'Catalog\ManagementsController@executorsEmpty' )->name( 'managements.executors.empty' );

            Route::get( 'managements/executors/search', 'Catalog\ManagementsController@executorsSearch' )->name( 'managements.executors.search' );

            Route::post( 'customers/search', 'Catalog\CustomersController@search' )->name( 'customers.search' );
            Route::get( 'customers/names', 'Catalog\CustomersController@names' )->name( 'customers.names' );

            Route::post( 'users/{user_id}/managements/search', 'Catalog\UsersController@managementsSearch' )->name( 'users.managements.search' );
            Route::get( 'users/{user_id}/logs', 'Catalog\UsersController@logs' )->name( 'users.logs' );
            Route::get( 'users/{user_id}/providers', 'Catalog\UsersController@providers' )->name( 'users.providers' );
            Route::put( 'users/{user_id}/providers/add', 'Catalog\UsersController@providersAdd' )->name( 'users.providers.add' );
            Route::delete( 'users/{user_id}/providers/del', 'Catalog\UsersController@providersDel' )->name( 'users.providers.del' );
            Route::get( 'users/{user_id}/perms', 'Catalog\UsersController@perms' )->name( 'users.perms' );
            Route::put( 'users/{user_id}/perms/update', 'Catalog\UsersController@permsUpdate' )->name( 'users.perms.update' );
            Route::put( 'users/{user_id}/roles/update', 'Catalog\UsersController@rolesUpdate' )->name( 'users.roles.update' );
            Route::get( 'users/{user_id}/managements', 'Catalog\UsersController@managements' )->name( 'users.managements' );
            Route::put( 'users/{user_id}/managements/add', 'Catalog\UsersController@managementsAdd' )->name( 'users.managements.add' );
            Route::delete( 'users/{user_id}/managements/del', 'Catalog\UsersController@managementsDel' )->name( 'users.managements.del' );
            Route::put( 'users/{user_id}/change-password', 'Catalog\UsersController@changePassword' )->name( 'users.change_password' );
            Route::put( 'users/{user_id}/upload-photo', 'Catalog\UsersController@uploadPhoto' )->name( 'users.upload_photo' );

        });

        Route::prefix( 'admin' )->group( function ()
        {

            Route::resource( 'roles', 'Admin\RolesController' );
            Route::resource( 'perms', 'Admin\PermsController' );
            Route::resource( 'logs', 'Admin\LogsController' );
            Route::resource( 'providers', 'Admin\ProvidersController' );
            Route::resource( 'sessions', 'Admin\SessionsController' );
            Route::resource( 'calls', 'Admin\CallsController' );

            Route::get( 'clear-cache', 'Admin\BaseController@clearCacheAndRedirect' )->name( 'admin.clear_cache' );

            Route::get( 'providers/{provider_id}/buildings', 'Admin\ProvidersController@buildings' )->name( 'providers.buildings' );
            Route::post( 'providers/{provider_id}/buildings/search', 'Admin\ProvidersController@buildingsSearch' )->name( 'providers.buildings.search' );
            Route::put( 'providers/{provider_id}/buildings/add', 'Admin\ProvidersController@buildingsAdd' )->name( 'providers.buildings.add' );
            Route::delete( 'providers/{provider_id}/buildings/del', 'Admin\ProvidersController@buildingsDel' )->name( 'providers.buildings.del' );
            Route::delete( 'providers/{provider_id}/buildings/empty', 'Admin\ProvidersController@buildingsEmpty' )->name( 'providers.buildings.empty' );
            Route::get( 'providers/{provider_id}/managements', 'Admin\ProvidersController@managements' )->name( 'providers.managements' );
            Route::post( 'providers/{provider_id}/managements/search', 'Admin\ProvidersController@managementsSearch' )->name( 'providers.managements.search' );
            Route::put( 'providers/{provider_id}/managements/add', 'Admin\ProvidersController@managementsAdd' )->name( 'providers.managements.add' );
            Route::delete( 'providers/{provider_id}/managements/del', 'Admin\ProvidersController@managementsDel' )->name( 'providers.managements.del' );
            Route::get( 'providers/{provider_id}/types', 'Admin\ProvidersController@types' )->name( 'providers.types' );
            Route::put( 'providers/{provider_id}/types/add', 'Admin\ProvidersController@typesAdd' )->name( 'providers.types.add' );
            Route::delete( 'providers/{provider_id}/types/del', 'Admin\ProvidersController@typesDel' )->name( 'providers.types.del' );
            Route::delete( 'providers/{provider_id}/types/empty', 'Admin\ProvidersController@typesEmpty' )->name( 'providers.types.empty' );
            Route::put( 'providers/{provider_id}/phones/add', 'Admin\ProvidersController@phonesAdd' )->name( 'providers.phones.add' );
            Route::delete( 'providers/{provider_id}/phones/del', 'Admin\ProvidersController@phonesDel' )->name( 'providers.phones.del' );
            Route::put( 'providers/{provider_id}/logo/upload', 'Admin\ProvidersController@uploadLogo' )->name( 'providers.logo.upload' );
            Route::delete( 'providers/{provider_id}/logo/delete', 'Admin\ProvidersController@deleteLogo' )->name( 'providers.logo.delete' );

            Route::get( 'roles/{role_id}/perms', 'Admin\RolesController@perms' )->name( 'roles.perms' );
            Route::put( 'roles/{role_id}/perms', 'Admin\RolesController@updatePerms' );

            Route::get( 'perms/{perm_id}/roles', 'Admin\PermsController@roles' )->name( 'perms.roles' );
            Route::put( 'perms/{perm_id}/roles', 'Admin\PermsController@updateRoles' );
            Route::get( 'perms/{perm_id}/users', 'Admin\PermsController@users' )->name( 'perms.users' );
            Route::post( 'perms/{perm_id}/users/search', 'Admin\PermsController@usersSearch' )->name( 'perms.users.search' );
            Route::put( 'perms/{perm_id}/users/add', 'Admin\PermsController@usersAdd' )->name( 'perms.users.add' );
            Route::delete( 'perms/{perm_id}/users/del', 'Admin\PermsController@usersDel' )->name( 'perms.users.del' );

        });

    });

    Route::prefix( 'asterisk' )->group( function ()
    {
        Route::get( 'queues', 'External\AsteriskController@queues' )->name( 'asterisk.queues' );
        Route::get( 'remove/{number}', 'External\AsteriskController@remove' )->name( 'asterisk.remove' );
        Route::post( 'call', 'External\AsteriskController@call' )->name( 'asterisk.call' );
    });

});