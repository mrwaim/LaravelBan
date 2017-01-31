<?php

namespace Klsandbox\LaravelBan;

use Illuminate\Support\ServiceProvider;

class LaravelBanServiceProvider extends ServiceProvider
{

    protected $commands = [
        'Klsandbox\LaravelBan\LaravelBan',
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/banned-keywords.php' => config_path('banned-keywords.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
    }
}
