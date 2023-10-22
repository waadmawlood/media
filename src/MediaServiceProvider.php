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
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('media.php'),
        ], 'media-config');

        $is_uuid = config('media.uuid', false);
        $path = $is_uuid ? '/../database/migrations/create_media_uuid_table.php.stub' : '/../database/migrations/create_media_table.php.stub';
        if (empty(glob(database_path('migrations/*_create_media_table.php')))) {
            $this->publishes([
                __DIR__ . $path => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_media_table.php'),
            ], 'media-migrations');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'media');
        $this->commands([
            MediaLinkCommand::class,
            MediaPrune::class,
        ]);
    }
}
