<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Waad\Media\MediaServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->useStoragePath(__DIR__.'/temp');

        if (! file_exists(__DIR__.'/temp/app/public/upload')) {
            mkdir(__DIR__.'/temp/app/public/upload', 0775, true);
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            MediaServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Load package config
        $app['config']->set('media', require __DIR__.'/../config/config.php');

        // Storage config
        $app['config']->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => __DIR__.'/temp/app/public',
            'url' => 'http://localhost/storage',
            'visibility' => 'public',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/create_media_table.php');
    }

    protected function tearDown(): void
    {
        $this->cleanupTempFiles();

        parent::tearDown();
    }

    protected function cleanupTempFiles()
    {
        if (file_exists(__DIR__.'/temp')) {
            try {
                $this->rrmdir(__DIR__.'/temp');
            } catch (\Exception $e) {
                // Ignore cleanup errors
            }
        }
    }

    protected function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (is_dir($dir.'/'.$object)) {
                        $this->rrmdir($dir.'/'.$object);
                    } else {
                        unlink($dir.'/'.$object);
                    }
                }
            }
            rmdir($dir);
        }
    }
}
