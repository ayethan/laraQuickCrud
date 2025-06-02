<?php

namespace Atk\LaraQuickCrud;

use Illuminate\Support\ServiceProvider;
use Atk\LaraQuickCrud\Console\Commands\LaraQuickCrudCommand;


class CrudGeneratorServiceProvider extends ServiceProvider
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
        if ($this->app->runningInConsole()) {
            $this->commands([
                LaraQuickCrudCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/laraquickcrud.php' => config_path('laraquickcrud.php'),
        ], 'laraquickcrud-config');

        $this->publishes([
            __DIR__.'/../stubs' => resource_path('stubs/laraquickcrud'),
        ], 'laraquickcrud-stubs');
    }
}
