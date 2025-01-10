<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Waad\Media\MediaServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Configure storage path
        $this->app->useStoragePath(__DIR__.'/temp');
        if (! file_exists(__DIR__.'/temp')) {
            mkdir(__DIR__.'/temp', 0775, true);
        }

        // Create storage directories
        if (! file_exists(__DIR__.'/temp/app/public/upload')) {
            mkdir(__DIR__.'/temp/app/public/upload', 0775, true);
        }

        // Clear any leftover temporary files
        $this->cleanupTempFiles();
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
        // $app['config']->set('media.uuid', true);

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
        // $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/create_media_uuid_table.php');
    }

    protected function tearDown(): void
    {
        // Clean up temp storage and files
        $this->cleanupTempFiles();

        // Close database connections and clear app instance
        if ($this->app) {
            foreach ($this->app->make('db')->getConnections() as $connection) {
                $connection->disconnect();
            }

            $this->app->flush();
            $this->app = null;
        }

        // Clean up memory
        gc_collect_cycles();

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
