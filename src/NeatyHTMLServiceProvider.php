<?php

namespace Lab1521\NeatyHTML;

use Illuminate\Support\ServiceProvider;
use Validator;

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
        Validator::extend('html', function ($attribute, $value, $parameters, $validator) {
            try {
                $neaty = new NeatyHTML($value);
                $validator->setCustomMessages(['body.html' => 'Empty HTML.']);

                return (bool) trim($neaty->tidyUp());
            } catch (NeatyDOMException $error) {
                $validator->setCustomMessages(['body.html' => $error->getMessage()]);
            }

            return false;
        });
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
