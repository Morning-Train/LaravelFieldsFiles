<?php

namespace MorningTrain\Laravel\Fields\Files;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/../config/filepond.php' => config_path('filepond.php'),
            ], 'mt-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/2019_05_04_094221_create_files_table.php' => database_path('migrations/2019_05_04_094221_create_files_table.php'),
            ], 'mt-migrations');

        }
    }

}
