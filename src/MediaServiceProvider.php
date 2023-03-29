<?php

namespace Waad\Media;

use Illuminate\Support\ServiceProvider;
use Waad\Media\Commands\MediaLinkCommand;
use Waad\Media\Commands\MediaPrune;

class MediaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'media');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MediaLinkCommand::class,
                MediaPrune::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('media.php'),
        ], 'config');

        if (!class_exists('CreateMediaTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_media_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_media_table.php'),
            ], 'migrations');
        }


    }
}
