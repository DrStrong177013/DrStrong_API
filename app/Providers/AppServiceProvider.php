<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    
    public function register(): void
    {
        if ($this->app->isLocal()) {
            $this->app->register(TelescopeApplicationServiceProvider::class);
            Telescope::night();
        }
    }



}
