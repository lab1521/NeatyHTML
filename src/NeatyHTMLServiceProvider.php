<?php

namespace Lab1521\NeatyHTML;

use Illuminate\Support\ServiceProvider;

class NeatyHTMLServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(NeatyHTML::class, function ($app) {
            return new NeatyHTML();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [NeatyHTML::class];
    }
}
