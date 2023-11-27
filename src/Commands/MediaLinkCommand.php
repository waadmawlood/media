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
        $link = $this->links();
        $shortcut = $this->laravel['config']['media.shortcut'];
        $link = realpath($link);

        if (file_exists(public_path($shortcut)) && !$this->isRemovableSymlink($link, $this->option('force'))) {
            $this->error("The [$shortcut] link already exists.");
        }

        if (is_link(public_path($shortcut))) {
            $this->laravel->make('files')->delete($link);
        }

        $this->laravel->make('files')->link($link, public_path($shortcut));

        $this->info("The [$link] link has been connected to [Media Link].");

        $this->info('The links have been created.');
    }

    /**
     * Get the symbolic links that are configured for the application.
     *
     * @return string
     */
    protected function links()
    {
        $disk = $this->laravel['config']['media.disk'];
        $root = $this->laravel['config']["filesystems.disks.$disk.root"];
        $link = $this->laravel['config']['media.path'];

        $path = $root . DIRECTORY_SEPARATOR . $link;

        if (!is_dir($path)) {
            mkdir($path, 775);
        }

        if (blank($path)) {
            storage_path(join(DIRECTORY_SEPARATOR, array('app', 'public', 'upload')));
        }

        return $path;
    }

    /**
     * Determine if the provided path is a symlink that can be removed.
     */
    protected function isRemovableSymlink(string $link, bool $force): bool
    {
        return is_link($link) && $force;
    }
}
