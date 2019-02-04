<?php

//Auth::routes();

Route::prefix( 'error' )->group( function ()
{
    Route::any( '404', 'ErrorsController@error404' )->name( 'error.404' );
    Route::any( '403', 'ErrorsController@error403' )->name( 'error.403' );
    Route::any( '423', 'ErrorsController@error423' )->name( 'error.423' );
    Route::any( '429', 'ErrorsController@error429' )->name( 'error.429' );
    Route::any( '500', 'ErrorsController@error500' )->name( 'error.500' );
});

Route::any( '/bot/telegram/{token}', 'BotController@telegram' );

Route::group( [ 'middleware' => 'api' ], function ()
{

    Route::prefix( 'rest' )->group( function ()
    {

        Route::any( '/', 'RestController@index' );

        Route::post( 'call', 'RestController@createOrUpdateCallDraft' );
        Route::post( 'customer', 'RestController@customer' );
        Route::post( 'user', 'RestController@user' );
        Route::any( 'ticket-call', 'RestController@ticketCall' );

        Route::any( 'phone-auth', 'RestController@phoneAuth' );

        Route::prefix( 'users' )->group( function ()
        {
            Route::any( 'addresses', 'UserController@addresses' );
        });

    });

    Route::prefix( 'asterisk' )->group( function ()
    {
        Route::get( 'queues/{queue?}', 'External\AsteriskController@queues' )->name( 'asterisk.queues' );
        Route::post( 'queues/{queue}', 'External\AsteriskController@queuesView' )->name( 'asterisk.queues' );
        Route::get( 'remove/{number}', 'External\AsteriskController@remove' )->name( 'asterisk.remove' );
        Route::post( 'call', 'External\AsteriskController@call' )->name( 'asterisk.call' );
    });

    Route::prefix( 'devices' )->group( function ()
    {
        //Route::get( '{route}', 'DeviceController@index' );
        Route::any( 'auth', 'DeviceController@auth' )->name( 'devices.auth' );
        Route::any( 'tickets', 'DeviceController@tickets' )->name( 'devices.tickets' );
        Route::any( 'updates', 'DeviceController@updates' )->name( 'devices.updates' );
        Route::any( 'contacts', 'DeviceController@contacts' )->name( 'devices.contacts' );
        Route::any( 'calls', 'DeviceController@calls' )->name( 'devices.calls' );
        Route::any( 'call', 'DeviceController@call' )->name( 'devices.call' );
        Route::any( 'position', 'DeviceController@position' )->name( 'devices.position' );
        Route::any( 'complete', 'DeviceController@complete' )->name( 'devices.complete' );
        Route::any( 'in-process', 'DeviceController@inProcess' )->name( 'devices.in_process' );
        Route::any( 'comment', 'DeviceController@comment' )->name( 'devices.comment' );
        Route::any( 'clear-cache', 'DeviceController@clearCache' )->name( 'devices.clear_cache' );
        Route::get( 'get/phone', 'DeviceController@getPhone' )->name( 'devices.get_phone' );
    });

});

Route::group( [ 'middleware' => [ 'web', 'srm' ] ], function ()
{

    Route::get( 'login', 'Auth\LoginController@showLoginForm' )->name( 'login' );
    Route::post( 'login', 'Auth\LoginController@login' );

    // Registration Routes...
    Route::get( 'register', 'Auth\RegisterController@showRegistrationForm' )->name( 'register' );
    Route::post( 'register', 'Auth\RegisterController@register' );

    // Password Reset Routes...
    Route::get( 'forgot', 'Auth\ForgotPasswordController@showLinkRequestForm' )->name( 'forgot' );
    Route::post( 'forgot', 'Auth\ForgotPasswordController@sendResetLinkEmail' )->name( 'password.email' );
    Route::get( 'reset/{token}', 'Auth\ResetPasswordController@showResetForm' )->name( 'reset' );
    Route::post( 'reset', 'Auth\ResetPasswordController@reset' );

    Route::post( '/pickup-call', 'ProfileController@pickupCall' )->name( 'pickup-call' );
    Route::resource( '/news', 'NewsController' );
    Route::get( '/rss', 'NewsController@rss' )->name( 'news.rss' );

    Route::group( [ 'middleware' => 'auth' ], function ()
    {

        Route::get( 'logout', 'Auth\LoginController@logout' )->name( 'logout' );

        Route::get( '/', 'HomeController@index' )->name( 'home' );
        Route::get( '/about', 'HomeController@about' )->name( 'about' );
        Route::get( '/files/download', 'FilesController@download' )->name( 'files.download' );

        Route::get( '/blank', 'HomeController@blank' )->name( 'blank' );

        Route::get( 'clear-cache', 'Operator\BaseController@clearCacheAndRedirect' )->name( 'tickets.clear_cache' );

        Route::get( '/comment', 'CommentsController@form' )->name( 'comments.form' );
        Route::post( '/comment', 'CommentsController@store' )->name( 'comments.store' );
        Route::post( '/comment/delete', 'CommentsController@delete' )->name( 'comments.delete' );
        Route::get( '/comment/fix', 'CommentsController@fix' );

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
            Route::get( 'search/form', 'Operator\WorksController@searchForm' )->name( 'works.search.form' );
            Route::post( 'buildings/search', 'Operator\WorksController@buildingsSearch' )->name( 'works.buildings.search' );
            Route::get( 'export', 'Operator\WorksController@export' )->name( 'works.export' );
            Route::get( 'report', 'Operator\WorksController@report' )->name( 'works.report' );
        });

        Route::prefix( 'tickets' )->group( function ()
        {

            Route::get( 'calendar/{date}', 'Operator\TicketsController@calendar' )->name( 'tickets.calendar' );
            Route::post( 'calendar', 'Operator\TicketsController@calendarData' )->name( 'tickets.calendar_data' );

            Route::get( '{ticket_id}/neighbors', 'Operator\TicketsController@neighborsTickets' )->name( 'tickets.neighbors' );
            Route::get( '{ticket_id}/address', 'Operator\TicketsController@addressTickets' )->name( 'tickets.address' );
            Route::get( '{ticket_id}/customers', 'Operator\TicketsController@customersTickets' )->name( 'tickets.customers' );
            Route::get( '{ticket_id}/works', 'Operator\TicketsController@works' )->name( 'tickets.works' );
            Route::get( '{ticket_management_id}/services', 'Operator\TicketsController@services' )->name( 'tickets.services' );

            Route::get( '{ticket_id}/postpone', 'Operator\TicketsController@postpone' )->name( 'tickets.postpone' );
            Route::get( 'postponed', 'Operator\TicketsController@getPostponed' )->name( 'tickets.postponed' );
            Route::post( 'postponed/{ticket_id}', 'Operator\TicketsController@postPostponed' )->name( 'tickets.postponed.update' );
            Route::get( '{ticket_id}/progress', 'Operator\TicketsController@progress' )->name( 'tickets.progress' );

            Route::post( '{ticket_id}/select', 'Operator\TicketsController@select' )->name( 'tickets.select' );

            Route::get( 'export', 'Operator\TicketsController@export' )->name( 'tickets.export' );
            Route::get( 'rate/{ticket_management_id}', 'Operator\TicketsController@getRateForm' )->name( 'tickets.rate' );
            Route::post( 'rate/{ticket_management_id}', 'Operator\TicketsController@postRateForm' )->name( 'tickets.rate' );
            Route::post( '{ticket_id}/save', 'Operator\TicketsController@postSave' )->name( 'tickets.save' );
            Route::get( 'cancel/{ticket_id}', 'Operator\TicketsController@cancel' )->name( 'tickets.cancel' );
            Route::get( 'print/act/{ticket_id}/{ticket_management_id?}', 'Operator\TicketsController@act' )->name( 'tickets.act' );
            Route::get( 'print/waybill', 'Operator\TicketsController@waybill' )->name( 'tickets.waybill' );
            Route::post( 'owner', 'Operator\TicketsController@owner' )->name( 'tickets.owner' );
            Route::post( 'owner.cancel', 'Operator\TicketsController@ownerCancel' )->name( 'tickets.owner.cancel' );
            Route::get( 'search/form', 'Operator\TicketsController@searchForm' )->name( 'tickets.search.form' );
            Route::post( 'search', 'Operator\TicketsController@search' )->name( 'tickets.search' );
            Route::post( 'filter', 'Operator\TicketsController@filter' )->name( 'tickets.filter' );
            Route::post( '{ticket_id}/tags/add', 'Operator\TicketsController@addTag' )->name( 'tickets.tags.add' );
            Route::post( '{ticket_id}/tags/del', 'Operator\TicketsController@delTag' )->name( 'tickets.tags.del' );
            Route::post( 'change-status/{ticket_id}/{ticket_management_id?}', 'Operator\TicketsController@changeStatus' )->name( 'tickets.status' );
            Route::get( 'executor/select/{ticket_management_id?}', 'Operator\TicketsController@getExecutor' )->name( 'tickets.executor.select' );
            Route::post( 'executor/check', 'Operator\TicketsController@checkExecutor' )->name( 'tickets.executor.check' );
            Route::post( 'executor/{ticket_management_id}/save', 'Operator\TicketsController@postExecutor' )->name( 'tickets.executor.save' );
            Route::get( 'managements/{ticket_management_id}', 'Operator\TicketsController@getManagements' )->name( 'tickets.managements.select' );
            Route::put( 'managements/{ticket_management_id}', 'Operator\TicketsController@postManagements' )->name( 'tickets.managements.save' );
            Route::post( 'comment/{ticket_id}', 'Operator\TicketsController@comment' )->name( 'tickets.comment' );
            Route::get( 'clear-cache', 'Operator\TicketsController@clearCache' )->name( 'tickets.clear_cache' );
            Route::post( 'line/{id}', 'Operator\TicketsController@line' )->name( 'tickets.line' );
            Route::get( 'history/{ticket_id}', 'Operator\TicketsController@history' )->name( 'tickets.history' );
            Route::post( 'comments/{id?}', 'Operator\TicketsController@comments' )->name( 'tickets.comments' );
            Route::put( 'services/{ticket_management_id}', 'Operator\TicketsController@saveServices' )->name( 'tickets.services.save' );

        });

        Route::resource( 'works', 'Operator\WorksController' );
        Route::resource( 'tickets', 'Operator\TicketsController' );

        Route::get( '/tickets/{ticket_id}/{ticket_management_id?}', 'Operator\TicketsController@show' )->name( 'tickets.show' );

        Route::prefix( 'reports' )->group( function ()
        {
            Route::get( 'index', 'Operator\ReportsController@index' )->name( 'reports.index' );
            Route::get( 'executors', 'Operator\ReportsController@executors' )->name( 'reports.executors' );
            Route::get( 'rates', 'Operator\ReportsController@rates' )->name( 'reports.rates' );
            Route::get( 'addresses', 'Operator\ReportsController@addresses' )->name( 'reports.addresses' );
            Route::get( 'tickets', 'Operator\ReportsController@tickets' )->name( 'reports.tickets' );
            Route::get( 'operators', 'Operator\ReportsController@operators' )->name( 'reports.operators' );
            Route::get( 'types', 'Operator\ReportsController@types' )->name( 'reports.types' );
            Route::get( 'totals', 'Operator\ReportsController@totals' )->name( 'reports.totals' );
        });

        Route::prefix( 'maps' )->group( function ()
        {
            Route::resource( 'zones', 'Maps\ZonesController' );
            Route::get( 'tickets', 'Maps\MapsController@tickets' )->name( 'maps.tickets' );
            Route::get( 'works', 'Maps\MapsController@works' )->name( 'maps.works' );
            Route::post( 'zones/load', 'Maps\ZonesController@load' )->name( 'zones.load' );
            Route::get( 'positions', 'Maps\MapsController@positions' )->name( 'maps.positions' );
        });

        Route::prefix( 'data' )->group( function ()
        {
            Route::get( 'buildings', 'Operator\DataController@buildings' )->name( 'data.buildings' );
            Route::get( 'buildings/{building_id}/rooms', 'Operator\DataController@buildingsRooms' )->name( 'data.buildings.rooms' );
            Route::get( 'works-buildings', 'Operator\DataController@worksBuildings' )->name( 'data.works_buildings' );
            Route::get( 'positions', 'Operator\DataController@positions' )->name( 'data.positions' );
        });

        Route::prefix( 'catalog' )->group( function ()
        {

            Route::get( 'segments/tree', 'Catalog\SegmentsController@tree' )->name( 'segments.tree' );
            Route::get( 'segments/{segment_id}/buildings', 'Catalog\SegmentsController@buildings' )->name( 'segments.buildings' );
            Route::get( 'segments/clear-cache', 'Catalog\SegmentsController@clearCache' )->name( 'segments.clear.cache' );

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
            Route::post( 'buildings/{building_id}/store-rooms', 'Catalog\BuildingsController@storeRooms' )->name( 'buildings.store.rooms' );
            Route::get( 'buildings/export', 'Catalog\BuildingsController@export' )->name( 'buildings.export' );

            Route::post( 'types/json', 'Catalog\TypesController@json' )->name( 'types.json' );
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
            Route::get( 'managements/{management_id}/buildings/export', 'Catalog\ManagementsController@buildingsExport' )->name( 'managements.buildings.export' );
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
            Route::get( 'managements/{management_id}/act/{act_id}', 'Catalog\ManagementsController@act' )->name( 'managements.act' );

            Route::get( 'managements/executors/search', 'Catalog\ManagementsController@executorsSearch' )->name( 'managements.executors.search' );
            Route::get( 'managements/search/form', 'Catalog\ManagementsController@searchForm' )->name( 'managements.search.form' );
            Route::put( 'managements/{management_id}/contract', 'Catalog\ManagementsController@contract' )->name( 'managements.contract' );

            Route::post( 'customers/search', 'Catalog\CustomersController@search' )->name( 'customers.search' );
            Route::get( 'customers/names', 'Catalog\CustomersController@names' )->name( 'customers.names' );
            Route::get( 'customers/search/form', 'Catalog\CustomersController@searchForm' )->name( 'customers.search.form' );

            Route::get( 'rooms/{room_id}/info', 'Catalog\RoomsController@info' )->name( 'rooms.info' );

            Route::get( 'groups/select/buildings', 'Catalog\GroupsController@selectBuildings' )->name( 'groups.select.buildings' );
            Route::get( 'groups/{group_id}/buildings', 'Catalog\GroupsController@buildings' )->name( 'groups.buildings' );
            Route::post( 'groups/{group_id}/buildings/search', 'Catalog\GroupsController@buildingsSearch' )->name( 'groups.buildings.search' );
            Route::put( 'groups/{group_id}/buildings/add', 'Catalog\GroupsController@buildingsAdd' )->name( 'groups.buildings.add' );
            Route::put( 'groups/{group_id}/segments/add', 'Catalog\GroupsController@segmentsAdd' )->name( 'groups.segments.add' );
            Route::delete( 'groups/{group_id}/buildings/del', 'Catalog\GroupsController@buildingsDel' )->name( 'groups.buildings.del' );
            Route::delete( 'groups/{group_id}/buildings/empty', 'Catalog\GroupsController@buildingsEmpty' )->name( 'groups.buildings.empty' );

            Route::resource( 'managements', 'Catalog\ManagementsController' );
            Route::resource( 'executors', 'Catalog\ExecutorsController' );
            Route::resource( 'customers', 'Catalog\CustomersController' );
            Route::resource( 'types', 'Catalog\TypesController' );
            Route::resource( 'segments', 'Catalog\SegmentsController' );
            Route::resource( 'buildings', 'Catalog\BuildingsController' );
            Route::resource( 'groups', 'Catalog\GroupsController' );
            Route::resource( 'rooms', 'Catalog\RoomsController' );
            Route::resource( 'categories', 'Catalog\CategoriesController' );

        });

        Route::prefix( 'admin' )->group( function ()
        {

            Route::resource( 'users', 'Admin\UsersController' );
            Route::resource( 'roles', 'Admin\RolesController' );
            Route::resource( 'perms', 'Admin\PermsController' );
            Route::resource( 'logs', 'Admin\LogsController' );
            Route::resource( 'providers', 'Admin\ProvidersController' );
            Route::resource( 'sessions', 'Admin\SessionsController' );
            Route::resource( 'calls', 'Admin\CallsController' );
            Route::resource( 'subscriptions', 'Admin\SubscriptionsController' );

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
            Route::delete( 'providers/{provider_id}/phones/del', 'Admin\ProvidersController@phonesDel' )->name( 'providers.phones.del' );
            Route::get( 'providers/{provider_id}/phones/create', 'Admin\ProvidersController@phonesCreate' )->name( 'providers.phones.create' );
            Route::put( 'providers/{provider_id}/phones/store', 'Admin\ProvidersController@phonesStore' )->name( 'providers.phones.store' );
            Route::get( 'providers/{provider_id}/phones/{phone_id}/edit', 'Admin\ProvidersController@phonesEdit' )->name( 'providers.phones.edit' );
            Route::post( 'providers/{provider_id}/phones/{phone_id}/update', 'Admin\ProvidersController@phonesUpdate' )->name( 'providers.phones.update' );
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

            Route::post( 'users/search', 'Admin\UsersController@search' )->name( 'users.search' );
            Route::post( 'users/{user_id}/managements/search', 'Admin\UsersController@managementsSearch' )->name( 'users.managements.search' );
            Route::get( 'users/{user_id}/logs', 'Admin\UsersController@userLogs' )->name( 'users.logs' );
            Route::get( 'users/{user_id}/providers', 'Admin\UsersController@providers' )->name( 'users.providers' );
            Route::put( 'users/{user_id}/providers/add', 'Admin\UsersController@providersAdd' )->name( 'users.providers.add' );
            Route::delete( 'users/{user_id}/providers/del', 'Admin\UsersController@providersDel' )->name( 'users.providers.del' );
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

});