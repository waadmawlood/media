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
            __DIR__ . '/../config/config.php' => config_path('media.php'),
        ], 'config');

        $is_uuid = config('media.uuid', false);
        $path = $is_uuid ? '/../database/migrations/create_media_uuid_table.php.stub' : '/../database/migrations/create_media_table.php.stub';
        if (!class_exists('CreateMediaTable')) {
            $this->publishes([
                __DIR__ . $path => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_media_table.php'),
            ], 'migrations');
        }
    }
}
