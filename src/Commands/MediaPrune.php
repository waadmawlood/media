<?php

namespace Waad\Media\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Waad\Media\Media;
use Waad\Media\Services\MediaPrunableService;

class MediaPrune extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'media:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune media files and records that are older than the configured retention period';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $days = config('media.delete_file_after_day');

            if (! is_numeric($days)) {
                throw new Exception('The media.delete_file_after_day config value must be a number');
            }

            $days = (int) $days;
            $dateSubDays = now()->subDays($days);

            // Prune soft deleted records and their files
            $media = new Media;
            $model = DB::table($media->getTable());
            $mediaPrune = new MediaPrunableService($model, $dateSubDays);
            $mediaPrune->all()->paths()->delete();

            // Permanently delete old soft-deleted records
            $forceDeletedCount = $media->onlyTrashed()
                ->where('deleted_at', '<', $dateSubDays)
                ->forceDelete();

            $this->info("Successfully pruned media files and permanently deleted {$forceDeletedCount} records.");

            return 0;
        } catch (Exception $e) {
            $this->error("Failed to prune media: {$e->getMessage()}");

            return 1;
        }
    }
}
