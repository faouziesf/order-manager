<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configurer la durée du remember me à 1 an (525600 minutes)
        Config::set('session.lifetime', 525600);
        Config::set('session.expire_on_close', false);

        // S'assurer que le cookie remember dure 1 an
        $this->app['auth']->viaRemember(function () {
            return true;
        });
    }
}
