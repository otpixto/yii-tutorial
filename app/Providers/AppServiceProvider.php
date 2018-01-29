<?php

namespace App\Providers;

use App\Classes\SessionGuardExtended;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        \Auth::extend(
            'sessionExtended',
            function ($app) {
                $provider = new EloquentUserProvider($app['hash'], config('auth.providers.users.model'));
                return new SessionGuardExtended('sessionExtended', $provider, app()->make('session.store'), request());
            }
        );
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
