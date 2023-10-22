<?php

namespace Waad\Media\Commands;

use Illuminate\Console\Command;

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

        foreach ($links as $value) {
            $link = $value['path'];
            $shortcut = $value['shortcut'];

            if (file_exists(public_path($shortcut)) && !$this->isRemovableSymlink($link, $this->option('force'))) {
                if ($this->option('force')) {
                    unlink(public_path($shortcut));
                    $this->info("The existing [{$shortcut}] link has been removed.");
                } else {
                    $this->error("The [{$shortcut}] link already exists. Use the --force option to recreate it.");
                    continue;
                }
            }

            if (is_link(public_path($shortcut))) {
                $this->laravel->make('files')->delete($link);
            }

            $this->laravel->make('files')->link($link, public_path($shortcut));

            $this->info("The [{$link}] link has been connected to [{$shortcut}].");
        }

        $this->info('The links have been created.');
    }

    /**
     * Get the symbolic links that are configured for the application.
     *
     * @return array<string>
     */
    protected function links()
    {
        $disks = $this->laravel['config']['media.shortcut'];

        $links = array();
        foreach ($disks as $disk => $shortcut) {
            $root = $this->laravel['config']["filesystems.disks.$disk.root"];
            $links[] = ['path' => realpath($root), 'shortcut' => $shortcut];
        }

        return $links;
    }

    /**
     * Determine if the provided path is a symlink that can be removed.
     */
    protected function isRemovableSymlink(string $link, bool $force): bool
    {
        return is_link($link) && $force;
    }
}
