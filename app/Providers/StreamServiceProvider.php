<?php

namespace App\Providers;

use App\Classes\Stream;
use Illuminate\Support\ServiceProvider;

class StreamServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('stream', function () {
            return new Stream();
        });
    }
}
