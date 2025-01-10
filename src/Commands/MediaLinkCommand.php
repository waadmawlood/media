<?php

namespace Waad\Media\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MediaLinkCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'media:link
                {--force : Recreate existing symbolic links}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the media links configured for the storage media';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $links = $this->links();

        if (empty($links)) {
            $this->warn('No media shortcuts configured. Please check your media config file.');

            return;
        }

        foreach ($links as $value) {
            if (! isset($value['path']) || ! isset($value['shortcut'])) {
                $this->error('Invalid link configuration.');

                continue;
            }

            $link = $value['path'];
            $shortcut = $value['shortcut'];

            if (! file_exists($link)) {
                $this->error("The source path [{$link}] does not exist.");

                continue;
            }

            $shortcutPath = public_path($shortcut);

            if (file_exists($shortcutPath)) {
                if (! $this->option('force')) {
                    $this->error("The [{$shortcut}] link already exists. Use the --force option to recreate it.");

                    continue;
                }

                if (is_link($shortcutPath)) {
                    unlink($shortcutPath);
                    $this->info("The existing [{$shortcut}] link has been removed.");
                } else {
                    $this->error("The [{$shortcut}] exists but is not a symbolic link.");

                    continue;
                }
            }

            try {
                File::link($link, $shortcutPath);
                $this->info("The [{$link}] link has been connected to [{$shortcut}].");
            } catch (\Exception $e) {
                $this->error("Failed to create symbolic link: {$e->getMessage()}");
            }
        }

        $this->info('The links have been created.');
    }

    /**
     * Get the symbolic links that are configured for the application.
     *
     * @return array<int, array<string, string>>
     */
    protected function links(): array
    {
        $disks = $this->laravel['config']['media.shortcut'] ?? [];
        $links = [];

        foreach ($disks as $disk => $shortcut) {
            $root = $this->laravel['config']["filesystems.disks.$disk.root"] ?? null;
            if ($root && ($realPath = realpath($root))) {
                $links[] = ['path' => $realPath, 'shortcut' => $shortcut];
            }
        }

        return $links;
    }
}
